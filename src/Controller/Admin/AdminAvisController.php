<?php

namespace App\Controller\Admin;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/avis')]
final class AdminAvisController extends AbstractController
{
    private function getActivites(Connection $conn): array
    {
        return $conn->fetchAllAssociative('SELECT id, nom, type, lieu FROM activite ORDER BY nom');
    }

    #[Route('', name: 'admin_avis_activites')]
    public function activites(Connection $conn): Response
    {
        return $this->render('admin/avis/activites.html.twig', [
            'activites' => $this->getActivites($conn),
        ]);
    }

    #[Route('/activite/{activiteId}', name: 'admin_avis_list')]
    public function list(int $activiteId, AvisRepository $repo, Connection $conn): Response
    {
        $activite = $conn->fetchAssociative('SELECT id, nom, type, lieu FROM activite WHERE id = ?', [$activiteId]);

        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable.');
        }

        return $this->render('admin/avis/index.html.twig', [
            'activite' => $activite,
            'avis'     => $repo->findByActivite($activiteId),
            'moyenne'  => $repo->avgNoteByActivite($activiteId),
            'total'    => $repo->countByActivite($activiteId),
        ]);
    }

    #[Route('/activite/{activiteId}/new', name: 'admin_avis_new')]
    public function new(int $activiteId, Request $request, EntityManagerInterface $em, Connection $conn): Response
    {
        $activite = $conn->fetchAssociative('SELECT id, nom, type, lieu FROM activite WHERE id = ?', [$activiteId]);
        if (!$activite) {
            throw $this->createNotFoundException('Activité introuvable.');
        }

        $avis = new Avis();
        $avis->setActiviteId($activiteId);
        $avis->setActiviteNom($activite['nom']);

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($avis);
            $em->flush();
            $this->addFlash('success', 'Avis ajouté avec succès.');
            return $this->redirectToRoute('admin_avis_list', ['activiteId' => $activiteId]);
        }

        return $this->render('admin/avis/form.html.twig', [
            'form'     => $form,
            'activite' => $activite,
            'avis'     => null,
            'titre'    => 'Ajouter un avis',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_avis_edit')]
    public function edit(Avis $avis, Request $request, EntityManagerInterface $em, Connection $conn): Response
    {
        $activite = $conn->fetchAssociative('SELECT id, nom, type, lieu FROM activite WHERE id = ?', [$avis->getActiviteId()]);

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Avis modifié avec succès.');
            return $this->redirectToRoute('admin_avis_list', ['activiteId' => $avis->getActiviteId()]);
        }

        return $this->render('admin/avis/form.html.twig', [
            'form'     => $form,
            'activite' => $activite,
            'avis'     => $avis,
            'titre'    => 'Modifier l\'avis',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_avis_delete', methods: ['POST'])]
    public function delete(Avis $avis, EntityManagerInterface $em): Response
    {
        $activiteId = $avis->getActiviteId();
        $em->remove($avis);
        $em->flush();
        $this->addFlash('success', 'Avis supprimé.');
        return $this->redirectToRoute('admin_avis_list', ['activiteId' => $activiteId]);
    }

    #[Route('/{id}/show', name: 'admin_avis_show')]
    public function show(Avis $avis, Connection $conn): Response
    {
        $activite = $conn->fetchAssociative('SELECT id, nom, type, lieu FROM activite WHERE id = ?', [$avis->getActiviteId()]);

        return $this->render('admin/avis/show.html.twig', [
            'avis'     => $avis,
            'activite' => $activite,
        ]);
    }
}
