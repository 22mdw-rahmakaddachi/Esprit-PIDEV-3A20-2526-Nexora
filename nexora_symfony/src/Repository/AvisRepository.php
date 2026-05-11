<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /** Kept for template compatibility — returns all avis (no activite_id in DB) */
    public function findByActivite(int $activiteId): array
    {
        return $this->findAll();
    }

    public function countByActivite(int $activiteId): int
    {
        return $this->count([]);
    }

    public function avgNoteByActivite(int $activiteId): float
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(a.rating)')
            ->getQuery()
            ->getSingleScalarResult();
        return round((float) $result, 1);
    }

    public function findLatest(int $limit = 6): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
