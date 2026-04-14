<?php

namespace App\Repository;

use App\Entity\ParticipationDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipationDemande::class);
    }

    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.clientId = :cid')
            ->setParameter('cid', $clientId)
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByActivite(int $activiteId): array
    {
        return $this->createQueryBuilder('d')
            ->where('IDENTITY(d.activite) = :aid')
            ->setParameter('aid', $activiteId)
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()->getResult();
    }

    public function findExisting(int $activiteId, ?int $clientId): ?ParticipationDemande
    {
        if (!$clientId) return null;
        return $this->createQueryBuilder('d')
            ->where('IDENTITY(d.activite) = :aid')
            ->andWhere('d.clientId = :cid')
            ->setParameter('aid', $activiteId)
            ->setParameter('cid', $clientId)
            ->getQuery()->getOneOrNullResult();
    }
}
