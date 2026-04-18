<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Paiement;
use App\Entity\ParticipationDemande;
use App\Repository\CommandeRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Service\FlouciService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/paiement')]
final class PaiementController extends AbstractController
{
    /**
     * Lance le paiement Flouci pour une commande.
     * Appelé depuis la page de confirmation de commande.
     */
    #[Route('/initier/{commandeId}', name: 'app_paiement_initier')]
    public function initier(
        int $commandeId,
        CommandeRepository $commandeRepo,
        FlouciService $flouci,
        EntityManagerInterface $em
    ): Response {
        $commande = $commandeRepo->find($commandeId);

        if (!$commande) {
            $this->addFlash('error', 'Commande introuvable.');
            return $this->redirectToRoute('app_produits');
        }

        // Générer les URLs de retour
        $successUrl = $this->generateUrl('app_paiement_succes', ['commandeId' => $commandeId], UrlGeneratorInterface::ABSOLUTE_URL);
        $failUrl    = $this->generateUrl('app_paiement_echec',  ['commandeId' => $commandeId], UrlGeneratorInterface::ABSOLUTE_URL);
        $trackingId = 'COMMANDE_' . $commandeId . '_' . time();

        // Appel API Flouci
        $result = $flouci->initierPaiement(
            $commande->getTotal(),
            $successUrl,
            $failUrl,
            $trackingId
        );

        if (!$result['success']) {
            $this->addFlash('error', 'Erreur lors de l\'initialisation du paiement. Vérifiez vos clés Flouci dans .env');
            return $this->redirectToRoute('app_commande_confirmation', ['id' => $commandeId]);
        }

        // Enregistrer le paiement en base avec statut EN_ATTENTE
        $paiement = new Paiement();
        $paiement->setCommandeId($commandeId);
        $paiement->setMontant($commande->getTotal());
        $paiement->setMethodePaiement('Konnect (Flouci)');
        $paiement->setStatut('EN_ATTENTE');
        $paiement->setTransactionId($result['payment_id']);
        $paiement->setDateCreation(new \DateTime());
        $paiement->setDatePaiement(new \DateTime());
        $em->persist($paiement);

        // Mettre à jour le statut de la commande
        $commande->setStatut('paiement_en_cours');
        $em->flush();

        // Rediriger vers la page de paiement Flouci (ou simulation)
        if ($result['mode_test'] ?? false) {
            // Mode test : afficher la page de paiement simulée
            return $this->render('panier/paiement_simulation.html.twig', [
                'montant'    => $commande->getTotal(),
                'commandeId' => $commandeId,
                'successUrl' => $result['link'],
                'modeTest'   => true,
            ]);
        }

        return $this->redirect($result['link']);
    }

    /**
     * Callback après paiement réussi.
     */
    #[Route('/succes/{commandeId}', name: 'app_paiement_succes')]
    public function succes(
        int $commandeId,
        Request $request,
        CommandeRepository $commandeRepo,
        FlouciService $flouci,
        EntityManagerInterface $em
    ): Response {
        $commande  = $commandeRepo->find($commandeId);
        $paymentId = $request->query->get('payment_id');

        if ($commande && $paymentId) {
            // Vérifier le paiement auprès de Flouci
            $verification = $flouci->verifierPaiement($paymentId);
            $statut       = $verification['statut'] ?? 'INCONNU';

            if ($statut === 'SUCCESS') {
                // Mettre à jour le paiement en base
                $paiement = $em->getRepository(Paiement::class)->findOneBy([
                    'commandeId'    => $commandeId,
                    'transactionId' => $paymentId,
                ]);

                if ($paiement) {
                    $paiement->setStatut('VALIDE');
                    $paiement->setDatePaiement(new \DateTime());
                    $paiement->setReferenceExterne('KONNECT_' . time());
                }

                $commande->setStatut('payee');
                $em->flush();

                return $this->render('panier/paiement_succes.html.twig', [
                    'commande'  => $commande,
                    'paymentId' => $paymentId,
                ]);
            }
        }

        // Paiement non confirmé → rediriger vers échec
        return $this->redirectToRoute('app_paiement_echec', ['commandeId' => $commandeId]);
    }

    /**
     * Callback après paiement échoué ou annulé.
     */
    #[Route('/echec/{commandeId}', name: 'app_paiement_echec')]
    public function echec(int $commandeId, CommandeRepository $commandeRepo, EntityManagerInterface $em): Response
    {
        $commande = $commandeRepo->find($commandeId);

        if ($commande) {
            $commande->setStatut('paiement_echoue');

            // Mettre à jour le paiement en base
            $paiement = $em->getRepository(Paiement::class)->findOneBy(['commandeId' => $commandeId]);
            if ($paiement && $paiement->getStatut() === 'EN_ATTENTE') {
                $paiement->setStatut('ECHOUE');
            }

            $em->flush();
        }

        return $this->render('panier/paiement_echec.html.twig', ['commande' => $commande]);
    }

    // ─── PAIEMENT ACTIVITÉ ───────────────────────────────────────────────────

    #[Route('/activite/initier/{demandeId}', name: 'app_paiement_activite_initier')]
    public function initierActivite(
        int $demandeId,
        ParticipationDemandeRepository $demandeRepo,
        FlouciService $flouci,
        EntityManagerInterface $em
    ): Response {
        $demande = $demandeRepo->find($demandeId);

        if (!$demande || $demande->getStatut() !== ParticipationDemande::STATUT_ACCEPTEE) {
            $this->addFlash('error', 'Demande introuvable ou non acceptée.');
            return $this->redirectToRoute('app_mes_activites');
        }

        if ($demande->getPaiementEffectue()) {
            $this->addFlash('info', 'Cette activité est déjà payée.');
            return $this->redirectToRoute('app_mes_activites');
        }

        $successUrl = $this->generateUrl('app_paiement_activite_succes', ['demandeId' => $demandeId], UrlGeneratorInterface::ABSOLUTE_URL);
        $failUrl    = $this->generateUrl('app_paiement_activite_echec',  ['demandeId' => $demandeId], UrlGeneratorInterface::ABSOLUTE_URL);
        $trackingId = 'ACTIVITE_' . $demandeId . '_' . time();

        $result = $flouci->initierPaiement(
            $demande->getActivite()->getPrix(),
            $successUrl,
            $failUrl,
            $trackingId
        );

        if (!$result['success']) {
            $this->addFlash('error', 'Erreur lors de l\'initialisation du paiement.');
            return $this->redirectToRoute('app_mes_activites');
        }

        // Enregistrer le paiement en base
        $paiement = new Paiement();
        $paiement->setDemandeId($demandeId);
        $paiement->setClientId($demande->getClientId());
        $paiement->setActiviteId($demande->getActivite()->getId());
        $paiement->setMontant($demande->getActivite()->getPrix());
        $paiement->setMethodePaiement('Konnect (Flouci)');
        $paiement->setStatut('EN_ATTENTE');
        $paiement->setTransactionId($result['payment_id']);
        $paiement->setReferenceTransaction('ACT-' . substr(md5($demandeId . time()), 0, 8));
        $paiement->setDateCreation(new \DateTime());
        $paiement->setDatePaiement(new \DateTime());
        $em->persist($paiement);
        $em->flush();

        // Mode test → page simulation, sinon redirection Flouci
        if ($result['mode_test'] ?? false) {
            return $this->render('panier/paiement_simulation.html.twig', [
                'montant'    => $demande->getActivite()->getPrix(),
                'commandeId' => 'ACT-' . $demandeId,
                'successUrl' => $result['link'],
                'modeTest'   => true,
            ]);
        }

        return $this->redirect($result['link']);
    }

    #[Route('/activite/succes/{demandeId}', name: 'app_paiement_activite_succes')]
    public function sucresActivite(
        int $demandeId,
        Request $request,
        ParticipationDemandeRepository $demandeRepo,
        FlouciService $flouci,
        EntityManagerInterface $em
    ): Response {
        $demande   = $demandeRepo->find($demandeId);
        $paymentId = $request->query->get('payment_id');

        if ($demande && $paymentId) {
            $verification = $flouci->verifierPaiement($paymentId);

            if ($verification['statut'] === 'SUCCESS') {
                $demande->setPaiementEffectue(true);

                $paiement = $em->getRepository(Paiement::class)->findOneBy([
                    'demandeId'     => $demandeId,
                    'transactionId' => $paymentId,
                ]);
                if ($paiement) {
                    $paiement->setStatut('VALIDE');
                    $paiement->setDatePaiement(new \DateTime());
                }

                $em->flush();

                return $this->render('participation/paiement_succes.html.twig', [
                    'demande'   => $demande,
                    'paymentId' => $paymentId,
                ]);
            }
        }

        return $this->redirectToRoute('app_paiement_activite_echec', ['demandeId' => $demandeId]);
    }

    #[Route('/activite/echec/{demandeId}', name: 'app_paiement_activite_echec')]
    public function echecActivite(int $demandeId, ParticipationDemandeRepository $repo, EntityManagerInterface $em): Response
    {
        $demande = $repo->find($demandeId);

        if ($demande) {
            $paiement = $em->getRepository(Paiement::class)->findOneBy(['demandeId' => $demandeId]);
            if ($paiement && $paiement->getStatut() === 'EN_ATTENTE') {
                $paiement->setStatut('ECHOUE');
                $em->flush();
            }
        }

        return $this->render('participation/paiement_echec.html.twig', ['demande' => $demande]);
    }
}
