<?php

namespace App\Controller;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
final class NotificationController extends AbstractController
{
    #[Route('/api/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function getUnread(NotificationService $notificationService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['count' => 0, 'notifications' => []]);
        }

        $notifications = $notificationService->getUnread($user->getId());

        // Formater pour le frontend
        $formatted = array_map(fn($n) => [
            'id'           => $n['id'],
            'type'         => $n['type'],
            'message'      => $n['message'],
            'related_id'   => $n['related_id'],
            'related_type' => 'publication',
            'is_read'      => (bool)$n['is_read'],
            'created_at'   => $n['created_at'],
        ], $notifications);

        return $this->json([
            'count'         => count($formatted),
            'notifications' => $formatted,
        ]);
    }

    #[Route('/mark-read/{id}', name: 'notification_mark_read', methods: ['POST'])]
    public function markAsRead(int $id, NotificationService $notificationService): JsonResponse
    {
        $notificationService->markAsRead($id);
        return $this->json(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'notification_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(NotificationService $notificationService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false], 401);
        }

        $notificationService->markAllAsRead($user->getId());
        return $this->json(['success' => true]);
    }

    #[Route('', name: 'notifications_index', methods: ['GET'])]
    public function index(NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $notificationService->getAll($user->getId());

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}
