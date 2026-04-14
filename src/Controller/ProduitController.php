<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/boutique', name: 'app_produits')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits'    => [],
            'categories'  => [],
            'categorieId' => null,
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_produit_show')]
    public function show(int $id): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => null,
        ]);
    }
}
