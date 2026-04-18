<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\NotificationRepository;
use App\Repository\PartenaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SseController extends AbstractController
{
    #[Route('/api/notifications/stream', name: 'api_notifications_stream')]
    public function streamNotifications(
        Request $request,
        NotificationRepository $notifRepo,
        PartenaireRepository $partenaireRepo
    ): StreamedResponse {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return new StreamedResponse(fn() => null, 401);
        }

        $userId   = $user->getId();
        $userType = match($user->getRole()) {
            'ROLE_PARTENAIRE' => 'PARTENAIRE',
            'ROLE_ADMIN'      => 'ADMIN',
            default           => 'CLIENT',
        };

        $lastId = (int) $request->query->get('lastId', 0);

        $response = new StreamedResponse(function() use ($notifRepo, $userId, $userType, $lastId) {
            // Récupérer les notifications non lues plus récentes que lastId
            $notifications = $notifRepo->findNewSince($userId, $userType, $lastId);

            $data = array_map(fn($n) => [
                'id'      => $n->getId(),
                'titre'   => $n->getTitre(),
                'message' => $n->getMessage(),
                'type'    => $n->getType(),
            ], $notifications);

            echo 'data: ' . json_encode($data) . "\n\n";
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
