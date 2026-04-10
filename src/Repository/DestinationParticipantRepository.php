<?php

namespace App\Repository;

use App\Entity\DestinationParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DestinationParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DestinationParticipant::class);
    }

    /**
     * Vérifie si un user a déjà rejoint une destination.
     */
    public function hasJoined(int $destinationId, int $userId): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.destination = :dest')
            ->andWhere('p.userId = :user')
            ->setParameter('dest', $destinationId)
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de participants d'une destination.
     */
    public function countByDestination(int $destinationId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.destination = :dest')
            ->setParameter('dest', $destinationId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
