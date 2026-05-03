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
            ->where('p.statut = :statut')
            ->setParameter('statut', 'actif')
            ->orderBy('p.dateAjout', 'DESC');

        if ($sousCategorieId) {
            $qb->andWhere('p.sousCategorieId = :sc')
               ->setParameter('sc', $sousCategorieId);
        }

        return $qb->getQuery()->getResult();
    }

    /** Recherche par mots-clés (nom, description, marque) */
    public function searchByKeywords(array $keywords): array
    {
        if (empty($keywords)) {
            return $this->findActifs();
        }

        $qb = $this->createQueryBuilder('p')
            ->where('p.statut = :statut')
            ->setParameter('statut', 'actif');

        $orX = $qb->expr()->orX();
        foreach ($keywords as $i => $kw) {
            $param = 'kw' . $i;
            $orX->add($qb->expr()->orX(
                $qb->expr()->like('LOWER(p.nom)',          ':' . $param),
                $qb->expr()->like('LOWER(p.description)',  ':' . $param),
                $qb->expr()->like('LOWER(p.marque)',       ':' . $param),
                $qb->expr()->like('LOWER(p.descriptionCourte)', ':' . $param)
            ));
            $qb->setParameter($param, '%' . strtolower($kw) . '%');
        }

        return $qb->andWhere($orX)
            ->orderBy('p.dateAjout', 'DESC')
            ->getQuery()
            ->getResult();
    }
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
