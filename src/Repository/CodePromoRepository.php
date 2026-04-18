<?php

namespace App\Repository;

use App\Entity\CodePromo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CodePromo>
 */
class CodePromoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodePromo::class);
    }

    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.partenaireId = :pid')
            ->setParameter('pid', $partenaireId)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findValidCode(string $code): ?CodePromo
    {
        $today = new \DateTime();
        return $this->createQueryBuilder('c')
            ->where('c.code = :code')
            ->andWhere('c.actif = 1')
            ->andWhere('c.dateDebut <= :today')
            ->andWhere('c.dateFin >= :today')
            ->setParameter('code', $code)
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
