<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    public function searchByLocalisation(string $query): array
    {
        return $this->createQueryBuilder('d')
            ->where('LOWER(d.localisation) LIKE LOWER(:q)')
            ->orWhere('LOWER(d.nom) LIKE LOWER(:q)')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }













     // ----------------------------
    // CREATE / UPDATE
    // ----------------------------
    public function save(Destination $entity, bool $flush = false): Destination
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $entity;
    }

    // ----------------------------
    // DELETE
    // ----------------------------
    public function remove(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ----------------------------
    // READ
    // ----------------------------
    public function findById(int $id): ?Destination
    {
        return $this->find($id);
    }

    public function findAllDestinations(): array
    {
        return $this->findAll();
    }

    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.nom LIKE :nom')
            ->setParameter('nom', "%$nom%")
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveDestinations(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.statut = :active')
            ->setParameter('active', 'active')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ----------------------------
    // MÉTHODES PERSONNALISÉES
    // ----------------------------
    public function search(string $term): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.nom LIKE :term OR d.description LIKE :term OR d.localisation LIKE :term')
            ->setParameter('term', "%$term%")
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
