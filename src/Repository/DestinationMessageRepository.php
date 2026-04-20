<?php

namespace App\Repository;

use App\Entity\DestinationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DestinationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DestinationMessage::class);
    }

    /**
     * Récupère les N derniers messages d'une destination, triés du plus ancien au plus récent.
     */
    public function findByDestination(int $destinationId, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.destination = :dest')
            ->setParameter('dest', $destinationId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
