<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function search(?string $q, ?string $role): array
    {
        $qb = $this->createQueryBuilder('u')->orderBy('u.id', 'DESC');

        if ($q) {
            $qb->andWhere('u.prenom LIKE :q OR u.nom LIKE :q OR u.email LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }
        if ($role) {
            $qb->andWhere('u.role = :role')->setParameter('role', $role);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByRole(): array
    {
        $rows = $this->createQueryBuilder('u')
            ->select('u.role, COUNT(u.id) as total')
            ->groupBy('u.role')
            ->getQuery()->getResult();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['role']] = (int) $r['total'];
        }
        return $map;
    }
}
