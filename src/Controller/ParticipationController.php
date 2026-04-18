<?php

namespace App\Controller;

use App\Entity\ParticipationDemande;
use App\Repository\ActiviteRepository;
use App\Repository\NotificationRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipationController extends AbstractController
{
    private function getFakeClient(): array
    {
        return ['id' => 47, 'nom' => 'Client Test', 'email' => 'client@test.com', 'telephone' => '55000000'];
    }

    #[Route('/api/badges', name: 'api_badges')]
    public function apiBadges(ParticipationDemandeRepository $demandeRepo, NotificationRepository $notifRepo, ActiviteRepository $activiteRepo): JsonResponse
    {
        $client = $this->getFakeClient();
        $notifNonLues = $notifRepo->countUnread($client['id'], 'CLIENT');

        $activites = $activiteRepo->findByPartenaire(8);
        $demandesEnAttente = 0;
        foreach ($activites as $a) {
            foreach ($demandeRepo->findByActivite($a->getId()) as $d) {
                if ($d->getStatut() === 'EN_ATTENTE') $demandesEnAttente++;
            }
        }

        return $this->json(['mesActivites' => $notifNonLues, 'demandes' => $demandesEnAttente]);
    }

    #[Route('/activites/{id}/inscrire', name: 'app_activite_inscrire', methods: ['POST'])]
    public function inscrire(int $id, Request $request, ActiviteRepository $activiteRepo, ParticipationDemandeRepository $demandeRepo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $activite = $activiteRepo->find($id);
        if (!$activite) throw $this->createNotFoundException();

        $client = $this->getFakeClient();

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

        // Notifier le partenaire — utiliser user_id du partenaire, pas partenaire_id
        $partenaire = $activite->getPartenaire();
        if ($partenaire) {
            $conn = $em->getConnection();
            $partenaireUserId = $conn->fetchOne('SELECT user_id FROM partenaire WHERE id = ?', [$partenaire->getId()]);
            if ($partenaireUserId) {
                $notif->notifyNouvelleDemandePartenaire($demande, (int)$partenaireUserId);
            }
        }

        $this->addFlash('success', '✅ Votre demande a été envoyée !');
        return $this->redirectToRoute('app_mes_activites');
    }

    #[Route('/mes-activites', name: 'app_mes_activites')]
    public function mesActivites(ParticipationDemandeRepository $demandeRepo, NotificationRepository $notifRepo): Response
    {
        $client = $this->getFakeClient();
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
    public function partenaireDemandes(ParticipationDemandeRepository $demandeRepo, ActiviteRepository $activiteRepo): Response
    {
        $activites = $activiteRepo->findByPartenaire(8);
        $demandes = [];
        foreach ($activites as $a) {
            $demandes = array_merge($demandes, $demandeRepo->findByActivite($a->getId()));
        }
        return $this->render('participation/partenaire_demandes.html.twig', ['demandes' => $demandes]);
    }

    #[Route('/partenaire/demandes/{id}/accepter', name: 'app_demande_accepter', methods: ['POST'])]
    public function accepter(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(ParticipationDemande::STATUT_ACCEPTEE);
        $em->flush();
        $notif->notifyAcceptation($demande);
        $this->addFlash('success', '✅ Demande acceptée.');
        return $this->redirectToRoute('app_partenaire_demandes');
    }

    #[Route('/partenaire/demandes/{id}/refuser', name: 'app_demande_refuser', methods: ['POST'])]
    public function refuser(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(ParticipationDemande::STATUT_REFUSEE);
        $em->flush();
        $notif->notifyRefus($demande);
        $this->addFlash('info', '❌ Demande refusée.');
        return $this->redirectToRoute('app_partenaire_demandes');
    }
}
