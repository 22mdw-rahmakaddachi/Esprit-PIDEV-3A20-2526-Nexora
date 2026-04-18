<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.partenaire = :pid')
            ->setParameter('pid', $partenaireId)
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()->getResult();
    }

    public function findWithFilters(?string $type, ?string $lieu): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.placesDisponibles > 0');
        if ($type) $qb->andWhere('a.type = :type')->setParameter('type', $type);
        if ($lieu) $qb->andWhere('a.lieu = :lieu')->setParameter('lieu', $lieu);
        return $qb->orderBy('a.dateCreation', 'DESC')->getQuery()->getResult();
    }

    public function findTypesVisibles(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('DISTINCT a.type')
            ->where('a.placesDisponibles > 0')
            ->getQuery()->getResult();
        return array_column($rows, 'type');
    }

    public function findVitrine(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.placesDisponibles > 0')
            ->orderBy('a.dateCreation', 'DESC')
            ->setMaxResults(4)
            ->getQuery()->getResult();
    }
}
