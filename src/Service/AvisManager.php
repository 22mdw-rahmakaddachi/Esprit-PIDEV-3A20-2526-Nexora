<?php

namespace App\Service;

use App\Entity\Avis;

class AvisManager
{
    /**
     * Valide les règles métier d'un avis.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Avis $avis): bool
    {
        // Règle 1 : Le titre est obligatoire et doit avoir entre 3 et 100 caractères
        if (empty($avis->getTitre())) {
            throw new \InvalidArgumentException('Le titre est obligatoire');
        }

        if (strlen($avis->getTitre()) < 3) {
            throw new \InvalidArgumentException('Le titre doit contenir au moins 3 caractères');
        }

        if (strlen($avis->getTitre()) > 100) {
            throw new \InvalidArgumentException('Le titre ne peut pas dépasser 100 caractères');
        }

        // Règle 2 : Le contenu est obligatoire et doit avoir entre 5 et 2000 caractères
        if (empty($avis->getContenu())) {
            throw new \InvalidArgumentException('Le commentaire est obligatoire');
        }

        if (strlen($avis->getContenu()) < 5) {
            throw new \InvalidArgumentException('Le commentaire doit contenir au moins 5 caractères');
        }

        if (strlen($avis->getContenu()) > 2000) {
            throw new \InvalidArgumentException('Le commentaire ne peut pas dépasser 2000 caractères');
        }

        // Règle 3 : La note doit être entre 1 et 5
        if ($avis->getRating() < 1 || $avis->getRating() > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5');
        }

        // Règle 4 : Le userId doit être positif
        if ($avis->getUserId() <= 0) {
            throw new \InvalidArgumentException('L\'utilisateur est invalide');
        }

        return true;
    }

    /**
     * Retourne un résumé textuel de l'avis.
     */
    public function getSummary(Avis $avis): string
    {
        return sprintf('[%d/5] %s — %s', $avis->getRating(), $avis->getTitre(), $avis->getAuteur());
    }

    /**
     * Vérifie si l'avis est positif (note >= 4).
     */
    public function isPositive(Avis $avis): bool
    {
        return $avis->getRating() >= 4;
    }

    /**
     * Vérifie si l'avis est négatif (note <= 2).
     */
    public function isNegative(Avis $avis): bool
    {
        return $avis->getRating() <= 2;
    }
}
