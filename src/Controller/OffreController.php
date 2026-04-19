<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offres')]
final class OffreController extends AbstractController
{
    #[Route('', name: 'app_offres')]
    public function index(OffreRepository $repo, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $pays   = $request->query->get('pays', '');

        $qb = $repo->createQueryBuilder('o')->orderBy('o.id', 'ASC');
        if ($search) {
            $qb->andWhere('o.titre LIKE :s OR o.pays LIKE :s OR o.description LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($pays) {
            $qb->andWhere('o.pays LIKE :p')->setParameter('p', '%' . $pays . '%');
        }

        $offres   = $qb->getQuery()->getResult();
        $total    = $repo->count([]);
        $paysList = array_unique(array_filter(array_map(fn($o) => $o->getPays(), $repo->findAll())));
        sort($paysList);

        return $this->render('offres/index.html.twig', [
            'offres'   => $offres,
            'total'    => $total,
            'paysList' => $paysList,
            'search'   => $search,
            'pays'     => $pays,
        ]);
    }

    #[Route('/actualiser', name: 'app_offres_actualiser', methods: ['POST'])]
    public function actualiser(EntityManagerInterface $em): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $jsonPath   = $projectDir . '/public/offres.json';
        $scriptPath = $projectDir . '/scraping_tunisair.py';

        // ── Lancer le script Python ──
        if (file_exists($scriptPath)) {
            $output   = [];
            $exitCode = 0;
            exec('py -X utf8 ' . escapeshellarg($scriptPath) . ' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Erreur scraping : ' . implode(' | ', array_slice($output, -3)),
                ], 500);
            }
        }

        // ── Lire le JSON généré ──
        if (!file_exists($jsonPath)) {
            return $this->json(['success' => false, 'message' => 'Fichier offres.json introuvable.'], 404);
        }
        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data) {
            return $this->json(['success' => false, 'message' => 'JSON invalide.'], 400);
        }

        $em->createQuery('DELETE FROM App\Entity\Offre o')->execute();
        $now = new \DateTime();
        foreach ($data as $item) {
            $offre = new Offre();
            $offre->setTitre($item['titre'] ?? '');
            $offre->setPrix($item['prix'] ?? null);
            $offre->setPays($item['pays'] ?? null);
            $offre->setDuree($item['duree'] ?? null);
            $offre->setDate($item['date'] ?? null);
            $offre->setDescription($item['description'] ?? null);
            $offre->setLien($item['lien'] ?? null);
            $offre->setImageUrl($item['image_url'] ?? null);
            $offre->setImageLocal($item['image_local'] ?? null);
            $offre->setUpdatedAt($now);
            $em->persist($offre);
        }
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => count($data) . ' offres récupérées et chargées.',
            'count'   => count($data),
        ]);
    }
}
