<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Service\NotificationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AvisController extends AbstractController
{
    public function __construct(private NotificationService $notif) {}
    #[Route('/avis', name: 'app_avis')]
    public function index(AvisRepository $avisRepo, Connection $conn): Response
    {
        $rows = $conn->fetchAllAssociative('SELECT id, nom, type FROM activite ORDER BY nom');

        $activites = [];
        foreach ($rows as $row) {
            $avis = $avisRepo->findByActivite((int) $row['id']);
            if (empty($avis)) continue;

            $row['avis']    = $avis;
            $row['moyenne'] = $avisRepo->avgNoteByActivite((int) $row['id']);
            $activites[]    = $row;
        }

        return $this->render('avis/index.html.twig', [
            'activites'   => $activites,
            'currentUser' => $this->getUser(),
            'allActivites' => $conn->fetchAllAssociative('SELECT id, nom FROM activite ORDER BY nom'),
        ]);
    }

    #[Route('/avis/new', name: 'app_avis_new_public', methods: ['POST'])]
    public function newPublic(Request $request, EntityManagerInterface $em, Connection $conn): Response
    {
        $user        = $this->getUser();
        $auteur      = $user ? $user->getFullName() : trim($request->request->get('auteur', ''));
        $activiteId  = (int) $request->request->get('activite_id');
        $note        = (int) $request->request->get('note', 5);
        $commentaire = trim($request->request->get('commentaire', ''));

        if (strlen($auteur) < 2 || !$activiteId || strlen($commentaire) < 10 || $note < 1 || $note > 5) {
            $this->addFlash('error', 'Données invalides. Vérifiez tous les champs.');
            return $this->redirectToRoute('app_avis');
        }

        $activite = $conn->fetchAssociative('SELECT id, nom FROM activite WHERE id = ?', [$activiteId]);
        if (!$activite) {
            $this->addFlash('error', 'Activité introuvable.');
            return $this->redirectToRoute('app_avis');
        }

        $image = null;
        $file  = $request->files->get('image');
        if ($file) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowed)) {
                $this->addFlash('error', 'Format image non supporté.');
                return $this->redirectToRoute('app_avis');
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Image trop lourde (max 5 Mo).');
                return $this->redirectToRoute('app_avis');
            }
            $filename = uniqid('avis_') . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avis', $filename);
            $image = $filename;
        }

        $avis = new Avis();
        $avis->setAuteur($auteur);
        $avis->setActiviteId($activiteId);
        $avis->setActiviteNom($activite['nom']);
        $avis->setNote($note);
        $avis->setCommentaire($commentaire);
        $avis->setImage($image);

        $em->persist($avis);
        $em->flush();

        // Notification globale — nouvel avis publié
        $this->notif->push(
            type:    'avis',
            message: "{$auteur} a laissé un avis ⭐{$note}/5 sur « {$activite['nom']} »",
            actor:   $auteur,
            refId:   $avis->getId(),
            refType: 'avis'
        );

        $this->addFlash('success', 'Votre avis a été publié avec succès.');
        return $this->redirectToRoute('app_avis');
    }
}
