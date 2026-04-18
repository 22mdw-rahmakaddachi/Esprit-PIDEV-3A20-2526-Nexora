<?php

namespace App\Repository;

use App\Entity\ProduitParent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitParent>
 */
class ProduitParentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitParent::class);
    }

    /** Produits actifs, filtrés par sous-catégorie si fournie */
    public function findActifs(?int $sousCategorieId = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('LOWER(p.statut) = :statut OR p.statut IS NULL')
            ->setParameter('statut', 'actif')
            ->orderBy('p.dateAjout', 'DESC');

        if ($sousCategorieId) {
            $qb->andWhere('p.sousCategorieId = :sc')
               ->setParameter('sc', $sousCategorieId);
        }

        return $qb->getQuery()->getResult();
    }

    /** Produits d'un partenaire */
    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.partenaireId = :pid')
            ->setParameter('pid', $partenaireId)
            ->orderBy('p.dateAjout', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
