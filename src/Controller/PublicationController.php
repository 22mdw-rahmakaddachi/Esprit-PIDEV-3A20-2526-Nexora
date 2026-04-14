<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publications')]
final class PublicationController extends AbstractController
{
    #[Route('/api', name: 'app_publications_api', methods: ['GET'])]
    public function api(Connection $conn): JsonResponse
    {
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC LIMIT 20'
        );
        foreach ($publications as &$pub) {
            $pub['reactions']    = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['commentaires'] = $conn->fetchAllAssociative(
                'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC',
                [$pub['id']]
            );
            $pub['total_reactions'] = array_sum(array_column($pub['reactions'], 'total'));
        }
        return $this->json($publications);
    }

    #[Route('', name: 'app_publications', methods: ['GET'])]
    public function index(Connection $conn): Response
    {
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC'
        );

        foreach ($publications as &$pub) {
            $pub['reactions']    = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['commentaires'] = $conn->fetchAllAssociative(
                'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC',
                [$pub['id']]
            );
            $pub['total_reactions'] = array_sum(array_column($pub['reactions'], 'total'));
        }

        return $this->render('publication/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/new', name: 'app_publication_new', methods: ['POST'])]
    public function new(Request $request, Connection $conn): Response
    {
        $auteur  = trim($request->request->get('auteur', ''));
        $contenu = trim($request->request->get('contenu', ''));
        $image   = null;

        if (strlen($auteur) < 2 || strlen($contenu) < 2) {
            $this->addFlash('error', 'Le nom et le contenu sont obligatoires (min. 2 caractères).');
            return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
        }

        // Gestion upload image
        $file = $request->files->get('image');
        if ($file) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowed)) {
                $this->addFlash('error', 'Format image non supporté (jpg, png, gif, webp).');
                return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Image trop lourde (max 5 Mo).');
                return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
            }
            $filename = uniqid('pub_') . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/publications', $filename);
            $image = $filename;
        }

        $conn->insert('publication', [
            'auteur'     => htmlspecialchars($auteur),
            'contenu'    => htmlspecialchars($contenu),
            'image'      => $image,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
    }

    #[Route('/{id}/commenter', name: 'app_publication_commenter', methods: ['POST'])]
    public function commenter(int $id, Request $request, Connection $conn): JsonResponse
    {
        $auteur  = trim($request->request->get('auteur', ''));
        $contenu = trim($request->request->get('contenu', ''));

        if (strlen($auteur) < 2 || strlen($contenu) < 2) {
            return $this->json(['error' => 'Données invalides.'], 400);
        }

        $conn->insert('publication_commentaire', [
            'publication_id' => $id,
            'auteur'         => htmlspecialchars($auteur),
            'contenu'        => htmlspecialchars($contenu),
            'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        $commentId = $conn->lastInsertId();

        return $this->json([
            'id'         => $commentId,
            'auteur'     => htmlspecialchars($auteur),
            'contenu'    => htmlspecialchars($contenu),
            'created_at' => (new \DateTime())->format('d/m/Y H:i'),
        ]);
    }

    #[Route('/{id}/reagir', name: 'app_publication_reagir', methods: ['POST'])]
    public function reagir(int $id, Request $request, Connection $conn): JsonResponse
    {
        $auteur  = trim($request->request->get('auteur', 'Anonyme'));
        $type    = $request->request->get('type', 'jaime');
        $types   = ['jaime', 'jadore', 'haha', 'wow', 'triste', 'grrr'];

        if (!in_array($type, $types)) {
            return $this->json(['error' => 'Réaction invalide.'], 400);
        }

        // Vérifie si une réaction existe déjà pour cet auteur
        $existing = $conn->fetchOne(
            'SELECT type_reaction FROM publication_reaction WHERE publication_id = ? AND auteur = ?',
            [$id, $auteur]
        );

        if ($existing === $type) {
            // Même réaction → on la retire (toggle)
            $conn->delete('publication_reaction', ['publication_id' => $id, 'auteur' => $auteur]);
        } elseif ($existing) {
            // Réaction différente → on la change
            $conn->update('publication_reaction', ['type_reaction' => $type], ['publication_id' => $id, 'auteur' => $auteur]);
        } else {
            // Nouvelle réaction
            $conn->insert('publication_reaction', [
                'publication_id' => $id,
                'auteur'         => $auteur,
                'type_reaction'  => $type,
                'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        }

        // Retourne les totaux mis à jour
        $reactions = $conn->fetchAllAssociative(
            'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
            [$id]
        );

        return $this->json(['reactions' => $reactions]);
    }
}
