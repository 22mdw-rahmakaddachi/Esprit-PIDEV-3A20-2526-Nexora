<?php

namespace App\Repository;

use App\Entity\ProduitParent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitParent>
 */
class ProduitParentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitParent::class);
    }

    public function findActifs(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.statut = :statut')
            ->setParameter('statut', 'ACTIF')
            ->orderBy('p.dateAjout', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
