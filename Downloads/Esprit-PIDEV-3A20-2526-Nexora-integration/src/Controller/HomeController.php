<?php

namespace App\Controller;

use App\Repository\ActiviteRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActiviteRepository $activiteRepo, ProduitParentRepository $produitRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'activites' => $activiteRepo->findVitrine(),
            'produits'  => $produitRepo->findActifs(),
        ]);
    }
}
