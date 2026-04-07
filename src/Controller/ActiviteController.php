<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActiviteController extends AbstractController
{
    #[Route('/activites', name: 'app_activites')]
    public function index(): Response
    {
        return $this->render('activite/index.html.twig', [
            'activites'  => [],
            'types'      => [],
            'lieux'      => [],
            'typeActif'  => null,
            'lieuActif'  => null,
        ]);
    }

    #[Route('/activites/{id}', name: 'app_activite_show')]
    public function show(int $id): Response
    {
        return $this->render('activite/show.html.twig', [
            'activite'         => null,
            'demandeExistante' => null,
        ]);
    }
}
