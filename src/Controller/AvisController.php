<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(AvisRepository $avisRepo, Connection $conn): Response
    {
        // Récupère toutes les activités
        $rows = $conn->fetchAllAssociative('SELECT id, nom, type FROM activite ORDER BY nom');

        // Pour chaque activité, charge ses avis et sa moyenne
        $activites = [];
        foreach ($rows as $row) {
            $avis = $avisRepo->findByActivite((int) $row['id']);
            if (empty($avis)) continue; // n'affiche que les activités avec avis

            $row['avis']    = $avis;
            $row['moyenne'] = $avisRepo->avgNoteByActivite((int) $row['id']);
            $activites[]    = $row;
        }

        return $this->render('avis/index.html.twig', [
            'activites' => $activites,
        ]);
    }
}
