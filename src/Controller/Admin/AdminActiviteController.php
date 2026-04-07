<?php

namespace App\Controller\Admin;

use App\Repository\CodePromoRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminActiviteController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        Request $request,
        PartenaireRepository $partenaireRepo,
        ProduitParentRepository $produitRepo,
        CodePromoRepository $promoRepo
    ): Response {
        $userId     = $request->getSession()->get('user_id');
        $partenaire = $userId ? $partenaireRepo->findOneBy(['user' => $userId]) : null;
        $produits   = $partenaire ? $produitRepo->findByPartenaire($partenaire->getId()) : [];
        $promos     = $partenaire ? $promoRepo->findByPartenaire($partenaire->getId()) : [];

        return $this->render('admin/dashboard.html.twig', [
            'totalActivites'    => 0,
            'totalReservations' => 0,
            'placesDisponibles' => 0,
            'demandesEnAttente' => 0,
            'activites'         => [],
            'demandes'          => [],
            'partenaire'        => $partenaire,
            'produits'          => $produits,
            'promos'            => $promos,
            'totalProduits'     => count($produits),
            'totalPromos'       => count($promos),
        ]);
    }

    #[Route('/activites', name: 'admin_activites')]
    public function index(): Response
    {
        return $this->render('admin/activite/index.html.twig', [
            'activites' => [],
        ]);
    }

    #[Route('/activites/new', name: 'admin_activite_new')]
    public function new(): Response
    {
        return $this->render('admin/activite/form.html.twig', [
            'form'     => null,
            'titre'    => 'Nouvelle activité',
            'activite' => null,
        ]);
    }

    #[Route('/activites/{id}/edit', name: 'admin_activite_edit')]
    public function edit(int $id): Response
    {
        return $this->render('admin/activite/form.html.twig', [
            'form'     => null,
            'titre'    => 'Modifier l\'activité',
            'activite' => null,
        ]);
    }

    #[Route('/activites/{id}/delete', name: 'admin_activite_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        return $this->redirectToRoute('admin_activites');
    }

    #[Route('/activites/{id}/show', name: 'admin_activite_show')]
    public function show(int $id): Response
    {
        return $this->render('admin/activite/show.html.twig', [
            'activite' => null,
            'demandes' => [],
        ]);
    }

    #[Route('/demandes', name: 'admin_demandes')]
    public function demandes(): Response
    {
        return $this->render('admin/demandes.html.twig', [
            'demandes' => [],
        ]);
    }
}
