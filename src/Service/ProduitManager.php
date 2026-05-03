<?php

namespace App\Service;

use App\Entity\ProduitParent;

class ProduitManager
{
    /**
     * Valide les règles métier d'un produit partenaire.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(ProduitParent $produit): bool
    {
        // Règle 1 : Le nom est obligatoire (min 3 caractères)
        if (empty($produit->getNom())) {
            throw new \InvalidArgumentException('Le nom du produit est obligatoire');
        }

        if (strlen($produit->getNom()) < 3) {
            throw new \InvalidArgumentException('Le nom doit contenir au moins 3 caractères');
        }

        if (strlen($produit->getNom()) > 200) {
            throw new \InvalidArgumentException('Le nom ne peut pas dépasser 200 caractères');
        }

        // Règle 2 : La description est obligatoire
        if (empty($produit->getDescription())) {
            throw new \InvalidArgumentException('La description complète est obligatoire');
        }

        // Règle 3 : La marque est obligatoire
        if (empty($produit->getMarque())) {
            throw new \InvalidArgumentException('La marque est obligatoire');
        }

        // Règle 4 : Le poids doit être positif ou zéro
        $poids = $produit->getPoidsKg();
        if ($poids !== null && $poids < 0) {
            throw new \InvalidArgumentException('Le poids doit être un nombre positif ou zéro');
        }

        // Règle 5 : Le statut doit être valide
        $statut = $produit->getStatut();
        if ($statut !== null && !in_array($statut, ['actif', 'inactif'])) {
            throw new \InvalidArgumentException('Le statut doit être "actif" ou "inactif"');
        }

        // Règle 6 : Le partenaireId doit être positif
        if ($produit->getPartenaireId() <= 0) {
            throw new \InvalidArgumentException('Le partenaire est invalide');
        }

        return true;
    }

    /**
     * Vérifie si le produit est actif.
     */
    public function isActif(ProduitParent $produit): bool
    {
        return $produit->getStatut() === 'actif';
    }

    /**
     * Vérifie si le produit a des variantes.
     */
    public function hasVariants(ProduitParent $produit): bool
    {
        return !$produit->getVariants()->isEmpty();
    }

    /**
     * Retourne le prix minimum parmi les variantes, ou null si aucune variante.
     */
    public function getPrixMin(ProduitParent $produit): ?float
    {
        return $produit->getPrixMin();
    }
}
