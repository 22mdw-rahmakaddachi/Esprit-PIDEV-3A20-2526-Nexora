<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\OffreRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ActiviteRepository $activiteRepo,
        ProduitParentRepository $produitRepo,
        ParticipationDemandeRepository $demandeRepo,
        OffreRepository $offreRepo
    ): Response {
        $user = $this->getUser();
        $mesActivites = [];

        if ($user instanceof Users) {
            $demandes = $demandeRepo->findByClient($user->getId());
            foreach ($demandes as $demande) {
                if ($demande->getActivite()) {
                    $mesActivites[] = [
                        'activite' => $demande->getActivite(),
                        'statut'   => $demande->getStatut(),
                        'date'     => $demande->getDateDemande(),
                    ];
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'activites'    => $activiteRepo->findVitrine(),
            'produits'     => $produitRepo->findActifs(),
            'mesActivites' => $mesActivites,
            'offres'       => $offreRepo->findBy([], ['id' => 'ASC'], 6),
            'offresTotal'  => $offreRepo->count([]),
        ]);
    }
}
