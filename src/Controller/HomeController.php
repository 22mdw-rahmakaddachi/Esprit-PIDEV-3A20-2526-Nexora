<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AvisRepository $avisRepo, Connection $conn): Response
    {
        $activites = $conn->fetchAllAssociative(
            'SELECT * FROM activite ORDER BY date_activite ASC LIMIT 4'
        );

        $dernieresPubs = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC LIMIT 20'
        );
        foreach ($dernieresPubs as &$pub) {
            $pub['reactions']    = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['commentaires'] = $conn->fetchAllAssociative(
                'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC',
                [$pub['id']]
            );
        }
        unset($pub);

        return $this->render('home/index.html.twig', [
            'activites'     => $activites,
            'produits'      => [],
            'dernierAvis'   => $avisRepo->findLatest(6),
            'dernieresPubs' => $dernieresPubs,
        ]);
    }
}
