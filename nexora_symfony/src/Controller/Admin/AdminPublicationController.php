<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/publications')]
final class AdminPublicationController extends AbstractController
{
    #[Route('', name: 'admin_publications')]
    public function index(Connection $conn): Response
    {
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC'
        );

        foreach ($publications as &$pub) {
            $pub['nb_commentaires'] = (int) $conn->fetchOne(
                'SELECT COUNT(*) FROM publication_commentaire WHERE publication_id = ?', [$pub['id']]
            );
            $pub['nb_reactions'] = (int) $conn->fetchOne(
                'SELECT COUNT(*) FROM publication_reaction WHERE publication_id = ?', [$pub['id']]
            );
        }

        return $this->render('admin/publication/index.html.twig', [
            'publications' => $publications,
            'total'        => count($publications),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_publication_delete', methods: ['POST'])]
    public function delete(int $id, Connection $conn): Response
    {
        // Supprimer les commentaires et réactions liés
        $conn->delete('publication_commentaire', ['publication_id' => $id]);
        $conn->delete('publication_reaction',    ['publication_id' => $id]);
        $conn->delete('publication',             ['id' => $id]);

        $this->addFlash('success', 'Publication supprimée.');
        return $this->redirectToRoute('admin_publications');
    }

    #[Route('/{id}/show', name: 'admin_publication_show')]
    public function show(int $id, Connection $conn): Response
    {
        $pub = $conn->fetchAssociative('SELECT * FROM publication WHERE id = ?', [$id]);
        if (!$pub) throw $this->createNotFoundException('Publication introuvable.');

        $pub['commentaires'] = $conn->fetchAllAssociative(
            'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC', [$id]
        );
        $pub['reactions'] = $conn->fetchAllAssociative(
            'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction', [$id]
        );

        return $this->render('admin/publication/show.html.twig', ['pub' => $pub]);
    }
}
