<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipationController extends AbstractController
{
    #[Route('/api/badges', name: 'api_badges')]
    public function apiBadges(): JsonResponse
    {
        return $this->json(['mesActivites' => 0, 'demandes' => 0]);
    }

    #[Route('/mes-activites', name: 'app_mes_activites')]
    public function mesActivites(): Response
    {
        return $this->render('participation/mes_activites.html.twig', [
            'demandes'      => [],
            'notifications' => [],
            'client'        => ['id' => 0, 'nom' => '', 'email' => '', 'telephone' => ''],
        ]);
    }

    #[Route('/notifications/lire/{id}', name: 'app_notif_lire')]
    public function lireNotif(int $id): Response
    {
        return $this->redirectToRoute('app_mes_activites');
    }

    #[Route('/activites/{id}/inscrire', name: 'app_activite_inscrire', methods: ['POST'])]
    public function inscrire(int $id): Response
    {
        return $this->redirectToRoute('app_mes_activites');
    }

    #[Route('/partenaire/demandes', name: 'app_partenaire_demandes')]
    public function partenaireDemandes(): Response
    {
        return $this->render('participation/partenaire_demandes.html.twig', [
            'demandes' => [],
        ]);
    }

    #[Route('/partenaire/demandes/{id}/accepter', name: 'app_demande_accepter', methods: ['POST'])]
    public function accepter(int $id): Response
    {
        return $this->redirectToRoute('app_partenaire_demandes');
    }

    #[Route('/partenaire/demandes/{id}/refuser', name: 'app_demande_refuser', methods: ['POST'])]
    public function refuser(int $id): Response
    {
        return $this->redirectToRoute('app_partenaire_demandes');
    }
}
