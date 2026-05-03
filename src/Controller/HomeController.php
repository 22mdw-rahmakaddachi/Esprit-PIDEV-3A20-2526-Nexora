<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\DestinationRepository;
use App\Repository\OffreRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Repository\ProduitParentRepository;
use Doctrine\DBAL\Connection;
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
        OffreRepository $offreRepo,
        DestinationRepository $destinationRepo,
        Connection $conn
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

        // Récupérer les publications récentes
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC LIMIT 6'
        );
        foreach ($publications as &$pub) {
            $pub['reactions'] = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['total_reactions'] = array_sum(array_column($pub['reactions'], 'total'));
            $pub['commentaires_count'] = $conn->fetchOne(
                'SELECT COUNT(*) FROM publication_commentaire WHERE publication_id = ?',
                [$pub['id']]
            );
        }

        return $this->render('home/index.html.twig', [
            'activites'    => $activiteRepo->findVitrine(),
            'produits'     => $produitRepo->findActifs(6),
            'mesActivites' => $mesActivites,
            'offres'       => $offreRepo->findBy([], ['id' => 'ASC'], 6),
            'offresTotal'  => $offreRepo->count([]),
            'destinations' => $destinationRepo->findBy([], ['id' => 'DESC'], 4),
            'publications' => $publications,
        ]);
    }
}
