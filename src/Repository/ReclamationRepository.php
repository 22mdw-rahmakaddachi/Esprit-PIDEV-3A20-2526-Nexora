<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('r')
            ->where('IDENTITY(r.client) = :cid')
            ->setParameter('cid', $clientId)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.activite', 'a')
            ->where('IDENTITY(a.partenaire) = :pid')
            ->setParameter('pid', $partenaireId)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()->getResult();
    }

    /** Compte les réclamations d'un partenaire */
    public function countByPartenaire(int $partenaireId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.activite', 'a')
            ->where('IDENTITY(a.partenaire) = :pid')
            ->setParameter('pid', $partenaireId)
            ->getQuery()->getSingleScalarResult();
    }

    /** Retourne TOUS les partenaires avec leur nombre de réclamations */
    public function findTousPartenairesAvecReclamations(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        return $conn->fetchAllAssociative(
            "SELECT
                COALESCE(p.id, 0)              as partenaire_id,
                COALESCE(p.nom_entreprise, '')  as nom_entreprise,
                u.email, u.prenom, u.nom, u.id as user_id,
                COUNT(r.id)                    as nb_reclamations
             FROM users u
             LEFT JOIN partenaire p ON p.user_id = u.id
             LEFT JOIN activite a   ON a.partenaire_id = p.id
             LEFT JOIN reclamation r ON r.activite_id = a.id
             WHERE u.role = 'ROLE_PARTENAIRE'
             GROUP BY u.id, u.email, u.prenom, u.nom, p.id, p.nom_entreprise
             ORDER BY nb_reclamations DESC, u.nom ASC"
        );
    }

    /** Retourne les partenaires avec plus de $seuil réclamations */
    public function findPartenairesEnZoneRouge(int $seuil = 3): array
    {
        $conn = $this->getEntityManager()->getConnection();
        return $conn->fetchAllAssociative(
            'SELECT p.id as partenaire_id, p.nom_entreprise,
                    u.email, u.prenom, u.nom,
                    COUNT(r.id) as nb_reclamations
             FROM reclamation r
             JOIN activite a ON a.id = r.activite_id
             JOIN partenaire p ON p.id = a.partenaire_id
             JOIN users u ON u.id = p.user_id
             GROUP BY p.id, p.nom_entreprise, u.email, u.prenom, u.nom
             HAVING COUNT(r.id) > :seuil
             ORDER BY nb_reclamations DESC',
            ['seuil' => $seuil]
        );
    }
}
