<?php

namespace App\Service;

use App\Entity\Commentaire;

class CommentaireManager
{
    /**
     * Valide les règles métier d'un commentaire.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Commentaire $commentaire): bool
    {
        // Règle 1 : Le contenu est obligatoire
        if (empty($commentaire->getContenu())) {
            throw new \InvalidArgumentException('Le commentaire est obligatoire');
        }

        // Règle 2 : Le contenu doit contenir au moins 2 caractères
        if (strlen($commentaire->getContenu()) < 2) {
            throw new \InvalidArgumentException('Le commentaire doit contenir au moins 2 caractères');
        }

        // Règle 3 : Le contenu ne peut pas dépasser 1000 caractères
        if (strlen($commentaire->getContenu()) > 1000) {
            throw new \InvalidArgumentException('Le commentaire ne peut pas dépasser 1000 caractères');
        }

        // Règle 4 : L'identifiant de l'avis doit être positif
        if ($commentaire->getAvisId() <= 0) {
            throw new \InvalidArgumentException('L\'identifiant de l\'avis doit être positif');
        }

        // Règle 5 : L'identifiant de l'utilisateur doit être positif
        if ($commentaire->getUserId() <= 0) {
            throw new \InvalidArgumentException('L\'utilisateur est invalide');
        }

        return true;
    }

    /**
     * Retourne un aperçu tronqué du contenu (50 caractères max).
     */
    public function getPreview(Commentaire $commentaire): string
    {
        $contenu = $commentaire->getContenu();

        if (strlen($contenu) <= 50) {
            return $contenu;
        }

        return substr($contenu, 0, 50) . '...';
    }

    /**
     * Vérifie si le commentaire appartient à un utilisateur donné.
     */
    public function isOwnedBy(Commentaire $commentaire, int $userId): bool
    {
        return $commentaire->getUserId() === $userId;
    }
}
