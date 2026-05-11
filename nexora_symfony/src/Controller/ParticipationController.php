<?php

namespace App\Controller;

use App\Entity\ParticipationDemande;
use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\AvisRepository;
use App\Repository\NotificationRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Repository\PartenaireRepository;
use App\Service\ActivityEmailService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipationController extends AbstractController
{
    private function getCurrentUser(): ?Users
    {
        $user = $this->getUser();
        return $user instanceof Users ? $user : null;
    }

    private function getClientData(): array
    {
        $user = $this->getCurrentUser();
        if ($user) {
            return [
                'id'        => $user->getId(),
                'nom'       => $user->getPrenom() . ' ' . $user->getNom(),
                'email'     => $user->getEmail(),
                'telephone' => (string) $user->getNum(),
            ];
        }
        // Fallback anonyme — ne devrait pas arriver si les routes sont protégées
        return ['id' => 0, 'nom' => 'Anonyme', 'email' => '', 'telephone' => ''];
    }

    /** Retourne l'ID du partenaire lié à l'utilisateur connecté, ou 0 */
    private function getPartenaireId(PartenaireRepository $partenaireRepo): int
    {
        $user = $this->getUser();
        if (!$user instanceof Users) return 0;
        $partenaire = $partenaireRepo->findOneBy(['user' => $user]);
        return $partenaire?->getId() ?? 0;
    }

    #[Route('/api/badges', name: 'api_badges')]
    public function apiBadges(ParticipationDemandeRepository $demandeRepo, NotificationRepository $notifRepo, ActiviteRepository $activiteRepo, PartenaireRepository $partenaireRepo): JsonResponse
    {
        $client = $this->getClientData();
        $notifNonLues = $notifRepo->countUnread($client['id'], 'CLIENT');

        $partenaireId = $this->getPartenaireId($partenaireRepo);
        $activites = $partenaireId ? $activiteRepo->findByPartenaire($partenaireId) : [];
        $demandesEnAttente = 0;
        foreach ($activites as $a) {
            foreach ($demandeRepo->findByActivite($a->getId()) as $d) {
                if ($d->getStatut() === 'EN_ATTENTE') $demandesEnAttente++;
            }
        }

        return $this->json(['mesActivites' => $notifNonLues, 'demandes' => $demandesEnAttente]);
    }

    #[Route('/activites/{id}/inscrire', name: 'app_activite_inscrire', methods: ['POST'])]
    public function inscrire(int $id, Request $request, ActiviteRepository $activiteRepo, ParticipationDemandeRepository $demandeRepo, EntityManagerInterface $em, NotificationService $notif, ActivityEmailService $emailService): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour vous inscrire à une activité.');
            return $this->redirectToRoute('app_login');
        }

        $activite = $activiteRepo->find($id);
        if (!$activite) throw $this->createNotFoundException();

        $client = $this->getClientData();

        // Sécurité supplémentaire : s'assurer que l'ID est valide
        if ($client['id'] <= 0) {
            $this->addFlash('danger', 'Impossible de récupérer votre identifiant. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }

        if ($demandeRepo->findExisting($id, $client['id'])) {
            $this->addFlash('warning', 'Vous avez déjà une demande pour cette activité.');
            return $this->redirectToRoute('app_activite_show', ['id' => $id]);
        }

        $demande = new ParticipationDemande();
        $demande->setActivite($activite)
                ->setClientId($client['id'])
                ->setClientNom($client['nom'])
                ->setClientEmail($client['email'])
                ->setClientTelephone($client['telephone'])
                ->setStatut(ParticipationDemande::STATUT_ATTENTE)
                ->setDateDemande(new \DateTime());

        if (!$activite->getAvecDate()) {
            $dateSouhaitee = $request->request->get('date_souhaitee');
            if ($dateSouhaitee) {
                $dt = new \DateTime($dateSouhaitee);
                if ($dt <= new \DateTime()) {
                    $this->addFlash('danger', 'La date choisie ne peut pas être dans le passé.');
                    return $this->redirectToRoute('app_activite_show', ['id' => $id]);
                }
                $activite->setDateActivite($dt);
            }
        }

        $em->persist($demande);
        $em->flush();

        $activite->setPlacesDisponibles(max(0, $activite->getPlacesDisponibles() - 1));
        $em->flush();

        // Notifier le partenaire via la relation ORM
        $partenaire = $activite->getPartenaire();
        if ($partenaire) {
            $partenaireUser = $partenaire->getUser();
            if ($partenaireUser) {
                $notif->notifyNouvelleDemandePartenaire($demande, $partenaireUser->getId());
            }
        }

        // Envoyer les emails
        $emailService->sendConfirmationDemande($demande);
        $emailService->sendNotificationPartenaire($demande);

        $this->addFlash('success', '✅ Votre demande a été envoyée !');
        return $this->redirectToRoute('app_mes_activites');
    }

    #[Route('/mes-activites', name: 'app_mes_activites')]
    public function mesActivites(ParticipationDemandeRepository $demandeRepo, NotificationRepository $notifRepo): Response
    {
        $client = $this->getClientData();
        return $this->render('participation/mes_activites.html.twig', [
            'demandes'      => $demandeRepo->findByClient($client['id']),
            'notifications' => $notifRepo->findByUser($client['id'], 'CLIENT'),
            'client'        => $client,
        ]);
    }

    #[Route('/notifications/lire/{id}', name: 'app_notif_lire')]
    public function lireNotif(int $id, NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        $notif = $repo->find($id);
        if ($notif) { $notif->setLue(true); $em->flush(); }
        return $this->redirectToRoute('app_mes_activites');
    }

    #[Route('/partenaire/demandes', name: 'app_partenaire_demandes')]
    public function partenaireDemandes(ParticipationDemandeRepository $demandeRepo, ActiviteRepository $activiteRepo, PartenaireRepository $partenaireRepo): Response
    {
        $partenaireId = $this->getPartenaireId($partenaireRepo);
        $activites = $partenaireId ? $activiteRepo->findByPartenaire($partenaireId) : [];
        $demandes = [];
        foreach ($activites as $a) {
            $demandes = array_merge($demandes, $demandeRepo->findByActivite($a->getId()));
        }
        return $this->render('participation/partenaire_demandes.html.twig', ['demandes' => $demandes]);
    }

    #[Route('/partenaire/demandes/{id}/accepter', name: 'app_demande_accepter', methods: ['POST'])]
    public function accepter(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif, ActivityEmailService $emailService): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(ParticipationDemande::STATUT_ACCEPTEE);
        $em->flush();
        $notif->notifyAcceptation($demande);
        $emailService->sendAcceptation($demande);
        $this->addFlash('success', '✅ Demande acceptée.');
        return $this->redirectToRoute('app_partenaire_demandes');
    }

    #[Route('/partenaire/demandes/{id}/refuser', name: 'app_demande_refuser', methods: ['POST'])]
    public function refuser(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif, ActivityEmailService $emailService): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(ParticipationDemande::STATUT_REFUSEE);
        $em->flush();
        $notif->notifyRefus($demande);
        $emailService->sendRefus($demande);
        $this->addFlash('info', '❌ Demande refusée.');
        return $this->redirectToRoute('app_partenaire_demandes');
    }

    // ── AVIS DU PARTENAIRE ────────────────────────────────────────────────────

    #[Route('/partenaire/avis', name: 'app_partenaire_avis')]
    public function partenaireAvis(
        AvisRepository $avisRepo,
        ActiviteRepository $activiteRepo,
        PartenaireRepository $partenaireRepo,
        \Doctrine\DBAL\Connection $conn
    ): Response {
        $avisList = $avisRepo->findLatest(100);

        // Charger les réponses existantes
        $reponses = [];
        if (!empty($avisList)) {
            $ids  = array_map(fn($a) => $a->getId(), $avisList);
            $rows = $conn->fetchAllAssociative(
                'SELECT * FROM avis_reponse WHERE avis_id IN (' . implode(',', $ids) . ')'
            );
            foreach ($rows as $row) {
                $reponses[$row['avis_id']] = $row;
            }
        }

        $total   = count($avisList);
        $moyenne = $total > 0
            ? round(array_sum(array_map(fn($a) => $a->getRating(), $avisList)) / $total, 1)
            : 0;

        return $this->render('partenaire/avis.html.twig', [
            'avisList' => $avisList,
            'reponses' => $reponses,
            'total'    => $total,
            'moyenne'  => $moyenne,
        ]);
    }
}
