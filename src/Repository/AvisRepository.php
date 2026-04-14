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

    public function findByActivite(int $activiteId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.activiteId = :id')
            ->setParameter('id', $activiteId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByActivite(int $activiteId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.activiteId = :id')
            ->setParameter('id', $activiteId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function avgNoteByActivite(int $activiteId): float
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(a.note)')
            ->where('a.activiteId = :id')
            ->setParameter('id', $activiteId)
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
