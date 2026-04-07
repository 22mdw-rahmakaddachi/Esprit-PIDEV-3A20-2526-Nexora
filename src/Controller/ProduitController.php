<?php

namespace App\Controller;

use App\Repository\ProduitParentRepository;
use App\Repository\SousCategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/boutique', name: 'app_produits')]
    public function index(
        Request $request,
        ProduitParentRepository $produitRepo,
        SousCategorieRepository $sousCatRepo
    ): Response {
        $sousCategorieId = $request->query->get('categorie') ? (int) $request->query->get('categorie') : null;

        return $this->render('produit/index.html.twig', [
            'produits'    => $produitRepo->findActifs($sousCategorieId),
            'categories'  => $sousCatRepo->findAll(),
            'categorieId' => $sousCategorieId,
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_produit_show')]
    public function show(int $id, ProduitParentRepository $produitRepo): Response
    {
        $produit = $produitRepo->find($id);

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
