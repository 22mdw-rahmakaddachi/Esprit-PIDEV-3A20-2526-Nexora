<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByUser(int $userId, string $userType = ''): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.userId = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnread(int $userId, string $userType = ''): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.userId = :uid')
            ->andWhere('n.isRead = false')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNewSince(int $userId, string $userType = '', int $lastId = 0): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.userId = :uid')
            ->andWhere('n.id > :lastId')
            ->andWhere('n.isRead = false')
            ->setParameter('uid', $userId)
            ->setParameter('lastId', $lastId)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUnreadByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.userId = :uid')
            ->andWhere('n.isRead = false')
            ->setParameter('uid', $userId)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
