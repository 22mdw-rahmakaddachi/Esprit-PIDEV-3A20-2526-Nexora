<?php

namespace App\Repository;

use App\Entity\ProduitVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitVariant>
 */
class ProduitVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitVariant::class);
    }

    public function findByProduitParent(int $produitParentId): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.produitParentId = :pid')
            ->setParameter('pid', $produitParentId)
            ->getQuery()
            ->getResult();
    }
}
