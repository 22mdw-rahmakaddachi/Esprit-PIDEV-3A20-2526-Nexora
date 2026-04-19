<?php

namespace App\Controller\Admin;

use App\Repository\CommandeNotificationRepository;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/notifications')]
final class AdminNotificationController extends AbstractController
{
    private function getPartenaireId(Request $request, PartenaireRepository $repo): int
    {
        $userId = $request->getSession()->get('user_id');
        if ($userId) {
            $p = $repo->findOneBy(['user' => $userId]);
            if ($p) return $p->getId();
        }
        $p = $repo->findOneBy([], ['id' => 'ASC']);
        return $p ? $p->getId() : 0;
    }

    #[Route('', name: 'admin_notifications')]
    public function index(Request $request, PartenaireRepository $pr, CommandeNotificationRepository $repo): Response
    {
        $pid = $this->getPartenaireId($request, $pr);
        return $this->render('admin/notifications/index.html.twig', [
            'notifications' => $repo->findByPartenaire($pid),
            'partenaire'    => $pr->find($pid),
        ]);
    }

    #[Route('/{id}/lire', name: 'admin_notification_lire', methods: ['POST'])]
    public function lire(int $id, CommandeNotificationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $notif = $repo->find($id);
        if ($notif) { $notif->setLue(true); $em->flush(); }
        return $this->json(['ok' => true]);
    }

    #[Route('/tout-lire', name: 'admin_notifications_tout_lire', methods: ['POST'])]
    public function toutLire(Request $request, PartenaireRepository $pr, CommandeNotificationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $pid = $this->getPartenaireId($request, $pr);
        foreach ($repo->findByPartenaire($pid, true) as $n) { $n->setLue(true); }
        $em->flush();
        return $this->json(['ok' => true]);
    }

    #[Route('/api/count', name: 'admin_notifications_count')]
    public function count(Request $request, PartenaireRepository $pr, CommandeNotificationRepository $repo): JsonResponse
    {
        $pid = $this->getPartenaireId($request, $pr);
        return $this->json(['count' => $repo->countNonLues($pid)]);
    }
}
