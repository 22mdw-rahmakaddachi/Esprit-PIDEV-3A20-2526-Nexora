<?php

namespace App\Controller\Admin;

use App\Repository\ActiviteRepository;
use App\Repository\CodePromoRepository;
use App\Repository\DestinationParticipantRepository;
use App\Repository\DestinationRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminActiviteController extends AbstractController
{
    public function __construct(
        private ActiviteRepository $activiteRepo,
        private DestinationRepository $destinationRepo,
        private DestinationParticipantRepository $participantRepo,
        private ProduitParentRepository $produitRepo,
        private CodePromoRepository $promoRepo
    ) {}

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // Totaux
        $totalActivites = $this->activiteRepo->count([]);
        $totalDestinations = $this->destinationRepo->count([]);
        $totalReservations = $this->participantRepo->count([]);
        
        // Calcul des places restantes totales
        $activitesAll = $this->activiteRepo->findAll();
        $placesLibres = 0;
        foreach ($activitesAll as $a) {
            $placesLibres += $a->getPlacesDisponibles();
        }

        // --- Données pour les Graphiques ---
        
        // 1. Top 5 Excursions (Popularité)
        $topDestinations = $this->destinationRepo->findBy([], ['nbParticipants' => 'DESC'], 5);
        $destLabels = [];
        $destData = [];
        foreach ($topDestinations as $d) {
            $destLabels[] = $d->getNom();
            $destData[]   = $d->getNbParticipants();
        }

        // 2. Répartition des Activités par Type
        $typesCount = [];
        foreach ($activitesAll as $a) {
            $t = $a->getType() ?? 'Autre';
            $typesCount[$t] = ($typesCount[$t] ?? 0) + 1;
        }

        return $this->render('admin/dashboard.html.twig', [
            'totalActivites'    => $totalActivites,
            'totalReservations' => $totalReservations,
            'placesDisponibles' => $placesLibres,
            'totalDestinations' => $totalDestinations,
            'demandesEnAttente' => 0, // À lier plus tard si nécessaire
            
            // Listes pour les tableaux
            'activites'         => $this->activiteRepo->findBy([], ['id' => 'DESC'], 5),
            'destinations'      => $this->destinationRepo->findBy([], ['id' => 'DESC'], 5),
            'produits'          => $this->produitRepo->findBy([], ['id' => 'DESC'], 5),
            'promos'            => $this->promoRepo->findBy([], ['id' => 'DESC'], 5),
            'demandes'          => [],
            
            // Stats pour Chart.js (JSON)
            'destLabels' => $destLabels,
            'destData'   => $destData,
            'activiteTypes' => array_keys($typesCount),
            'activiteCounts' => array_values($typesCount),
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
