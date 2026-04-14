<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActiviteController extends AbstractController
{
    #[Route('/activites', name: 'app_activites')]
    public function index(Connection $conn, Request $request): Response
    {
        $typeActif = $request->query->get('type');
        $lieuActif = $request->query->get('lieu');

        $qb = 'SELECT * FROM activite WHERE 1=1';
        $params = [];
        if ($typeActif) { $qb .= ' AND type = ?'; $params[] = $typeActif; }
        if ($lieuActif) { $qb .= ' AND lieu = ?'; $params[] = $lieuActif; }
        $qb .= ' ORDER BY date_activite ASC';

        $activites = $conn->fetchAllAssociative($qb, $params);
        $types     = array_unique(array_column($conn->fetchAllAssociative('SELECT DISTINCT type FROM activite ORDER BY type'), 'type'));
        $lieux     = array_unique(array_column($conn->fetchAllAssociative('SELECT DISTINCT lieu FROM activite ORDER BY lieu'), 'lieu'));

        return $this->render('activite/index.html.twig', [
            'activites' => $activites,
            'types'     => $types,
            'lieux'     => $lieux,
            'typeActif' => $typeActif,
            'lieuActif' => $lieuActif,
        ]);
    }

    #[Route('/activites/{id}', name: 'app_activite_show')]
    public function show(int $id, Request $request, AvisRepository $avisRepo, EntityManagerInterface $em, Connection $conn): Response
    {
        $activite = $conn->fetchAssociative('SELECT * FROM activite WHERE id = ?', [$id]);

        if (!$activite) {
            return $this->render('activite/show.html.twig', [
                'activite'         => null,
                'demandeExistante' => null,
                'avisList'         => [],
                'moyenne'          => 0,
                'totalAvis'        => 0,
                'avisForm'         => null,
            ]);
        }

        $avis = new Avis();
        $avis->setActiviteId($id);
        $avis->setActiviteNom($activite['nom']);
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($avis);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été publié. Merci !');
            return $this->redirectToRoute('app_activite_show', ['id' => $id, '_fragment' => 'avis']);
        }

        return $this->render('activite/show.html.twig', [
            'activite'         => $activite,
            'demandeExistante' => null,
            'avisList'         => $avisRepo->findByActivite($id),
            'moyenne'          => $avisRepo->avgNoteByActivite($id),
            'totalAvis'        => $avisRepo->countByActivite($id),
            'avisForm'         => $form,
        ]);
    }
}
