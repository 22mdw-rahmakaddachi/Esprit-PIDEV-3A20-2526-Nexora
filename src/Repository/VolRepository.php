<?php

namespace App\Repository;

use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vol>
 */
class VolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vol::class);
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('v.numero', 'ASC')
            ->getQuery()->getResult();
    }

    public function findEnVol(): array
    {
        return $this->findByStatut('En vol');
    }
}
