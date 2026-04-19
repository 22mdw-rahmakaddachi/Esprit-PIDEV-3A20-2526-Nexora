<?php

namespace App\Repository;

use App\Entity\CommandeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeNotification>
 */
class CommandeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeNotification::class);
    }

    public function findByPartenaire(int $partenaireId, bool $nonLueSeulement = false): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.partenaireId = :pid')
            ->setParameter('pid', $partenaireId)
            ->orderBy('n.createdAt', 'DESC');

        if ($nonLueSeulement) {
            $qb->andWhere('n.lue = false');
        }

        return $qb->getQuery()->getResult();
    }

    public function countNonLues(int $partenaireId): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.partenaireId = :pid')
            ->andWhere('n.lue = false')
            ->setParameter('pid', $partenaireId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
