<?php

namespace App\Controller\Admin;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/avis')]
final class AdminAvisController extends AbstractController
{
    #[Route('', name: 'admin_avis_activites')]
    public function activites(AvisRepository $repo, Request $request): Response
    {
        $search  = $request->query->get('search', '');
        $note    = $request->query->get('note', '');
        $sort    = $request->query->get('sort', 'date_desc');

        $qb = $repo->createQueryBuilder('a');

        if ($search) {
            $qb->andWhere('a.titre LIKE :s OR a.contenu LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($note !== '' && $note !== null) {
            $qb->andWhere('a.rating = :note')->setParameter('note', (int)$note);
        }

        match($sort) {
            'date_asc'   => $qb->orderBy('a.createdAt', 'ASC'),
            'note_desc'  => $qb->orderBy('a.rating', 'DESC'),
            'note_asc'   => $qb->orderBy('a.rating', 'ASC'),
            default      => $qb->orderBy('a.createdAt', 'DESC'),
        };

        $avisList = $qb->getQuery()->getResult();

        $avgResult = $repo->createQueryBuilder('a')->select('AVG(a.rating)')->getQuery()->getSingleScalarResult();
        $moyenne   = round((float)$avgResult, 1);

        return $this->render('admin/avis/activites.html.twig', [
            'avisList' => $avisList,
            'total'    => $repo->count([]),
            'moyenne'  => $moyenne,
            'search'   => $search,
            'note'     => $note,
            'sort'     => $sort,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_avis_delete', methods: ['POST'])]
    public function delete(Avis $avis, EntityManagerInterface $em): Response
    {
        $em->remove($avis);
        $em->flush();
        $this->addFlash('success', 'Avis supprimé.');
        return $this->redirectToRoute('admin_avis_activites');
    }

    #[Route('/{id}/show', name: 'admin_avis_show')]
    public function show(Avis $avis): Response
    {
        return $this->render('admin/avis/show.html.twig', [
            'avis'     => $avis,
            'activite' => null,
        ]);
    }
}
