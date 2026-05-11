<?php

namespace App\Controller;

use App\Entity\Vol;
use App\Repository\VolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vols')]
final class VolController extends AbstractController
{
    #[Route('', name: 'app_vols')]
    public function index(VolRepository $repo, Request $request): Response
    {
        $statut = $request->query->get('statut', '');
        $search = $request->query->get('search', '');

        $qb = $repo->createQueryBuilder('v')->orderBy('v.statut', 'ASC')->addOrderBy('v.numero', 'ASC');

        if ($statut) {
            $qb->andWhere('v.statut = :statut')->setParameter('statut', $statut);
        }
        if ($search) {
            $qb->andWhere('v.numero LIKE :s OR v.depart LIKE :s OR v.arrivee LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        $vols = $qb->getQuery()->getResult();
        $total = $repo->count([]);
        $lastUpdate = $repo->createQueryBuilder('v')->select('MAX(v.updatedAt)')->getQuery()->getSingleScalarResult();

        return $this->render('vols/index.html.twig', [
            'vols'        => $vols,
            'total'       => $total,
            'statuts'     => ['En vol', 'Atterri', 'Programmé', 'Retard'],
            'statutActif' => $statut,
            'search'      => $search,
            'lastUpdate'  => $lastUpdate ? new \DateTime($lastUpdate) : null,
            'stats'       => [
                'en_vol'    => $repo->count(['statut' => 'En vol']),
                'atterri'   => $repo->count(['statut' => 'Atterri']),
                'programme' => $repo->count(['statut' => 'Programmé']),
                'retard'    => $repo->count(['statut' => 'Retard']),
            ],
        ]);
    }

    #[Route('/actualiser', name: 'app_vols_actualiser', methods: ['POST'])]
    public function actualiser(EntityManagerInterface $em, VolRepository $repo): JsonResponse
    {
        $jsonPath = $this->getParameter('kernel.project_dir') . '/public/vols.json';

        if (!file_exists($jsonPath)) {
            return $this->json(['success' => false, 'message' => 'Fichier vols.json introuvable.'], 404);
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data) {
            return $this->json(['success' => false, 'message' => 'Fichier JSON invalide.'], 400);
        }

        // Vider la table et recharger
        $em->createQuery('DELETE FROM App\Entity\Vol v')->execute();

        $now = new \DateTime();
        foreach ($data as $item) {
            $vol = new Vol();
            $vol->setNumero($item['numero'] ?? '');
            $vol->setDepart($item['depart'] ?? '');
            $vol->setArrivee($item['arrivee'] ?? '');
            $vol->setStatut($item['statut'] ?? '');
            $vol->setHeureDepart($item['heure_depart'] ?? null);
            $vol->setHeureArrivee($item['heure_arrivee'] ?? null);
            $vol->setUpdatedAt($now);
            $em->persist($vol);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => count($data) . ' vols chargés avec succès.',
            'count'   => count($data),
            'time'    => $now->format('d/m/Y H:i:s'),
        ]);
    }

    #[Route('/api/live', name: 'api_vols_live')]
    public function apiLive(VolRepository $repo): JsonResponse
    {
        $vols = $repo->findBy([], ['numero' => 'ASC'], 6);
        return $this->json(array_map(fn($v) => [
            'numero'  => $v->getNumero(),
            'depart'  => $v->getDepart(),
            'arrivee' => $v->getArrivee(),
            'statut'  => $v->getStatut(),
        ], $vols));
    }
}
