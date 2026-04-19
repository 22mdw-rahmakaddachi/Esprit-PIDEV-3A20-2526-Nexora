<?php

namespace App\Controller;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(private NotificationService $notifService) {}

    #[Route('/api', name: 'notification_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        $since  = (int) $request->query->get('since', 0);
        $notifs = $this->notifService->getSince($since, 20);

        return $this->json([
            'notifications' => $notifs,
            'unread'        => $this->notifService->countUnread(),
        ]);
    }

    #[Route('/read', name: 'notification_read', methods: ['POST'])]
    public function markRead(): JsonResponse
    {
        $this->notifService->markAllRead();
        return $this->json(['ok' => true]);
    }
}
