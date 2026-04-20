<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/moderation')]
final class AdminModerationController extends AbstractController
{
    #[Route('', name: 'admin_moderation')]
    public function index(Connection $conn): Response
    {
        $warnings = $conn->fetchAllAssociative(
            'SELECT * FROM publication_warning ORDER BY created_at DESC'
        );

        // Group by user_id to show latest status per user
        $users = [];
        foreach ($warnings as $w) {
            $uid = $w['user_id'];
            if (!isset($users[$uid]) || $w['warning_count'] > $users[$uid]['warning_count']) {
                $users[$uid] = $w;
            }
        }

        return $this->render('admin/moderation/index.html.twig', [
            'users'    => array_values($users),
            'warnings' => $warnings,
        ]);
    }

    #[Route('/debloquer/{userId}', name: 'admin_moderation_debloquer', methods: ['POST'])]
    public function debloquer(int $userId, Connection $conn): Response
    {
        $conn->executeStatement(
            'UPDATE publication_warning SET is_blocked = 0, warning_count = 0 WHERE user_id = ?',
            [$userId]
        );

        $nom = $conn->fetchOne('SELECT CONCAT(prenom, " ", nom) FROM users WHERE id = ?', [$userId]);
        $this->addFlash('success', "✅ Utilisateur {$nom} débloqué. Il peut à nouveau publier.");
        return $this->redirectToRoute('admin_moderation');
    }

    #[Route('/supprimer/{id}', name: 'admin_moderation_supprimer', methods: ['POST'])]
    public function supprimer(int $id, Connection $conn): Response
    {
        $conn->delete('publication_warning', ['id' => $id]);
        $this->addFlash('success', 'Avertissement supprimé.');
        return $this->redirectToRoute('admin_moderation');
    }
}
