<?php

namespace App\Controller;

use App\Repository\DestinationRepository;
use App\Entity\Destination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/destinations')]
class DestinationClientController extends AbstractController
{
    public function __construct(private DestinationRepository $repo) {}

    // ========================= LIST CLIENT =========================
    #[Route('', name: 'client_destination_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');

        $destinations = $search
            ? $this->repo->searchByLocalisation($search)
            : $this->repo->findAllOrdered();

        return $this->render('destination/client/index.html.twig', [
            'destinations' => $destinations,
            'search'       => $search,
        ]);
    }

    // ========================= DETAILS =========================
    #[Route('/{id}', name: 'client_destination_show', methods: ['GET'])]
    public function show(Destination $destination): Response
    {
        return $this->render('destination/client/show.html.twig', [
            'destination' => $destination,
        ]);
    }
}
