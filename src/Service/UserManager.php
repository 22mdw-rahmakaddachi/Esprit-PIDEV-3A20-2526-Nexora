<?php

namespace App\Service;

use App\Entity\Users;

class UserManager
{
    /**
     * Valide les règles métier d'un utilisateur.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Users $user): bool
    {
        // Règle 1 : Le nom est obligatoire
        if (empty($user->getNom())) {
            throw new \InvalidArgumentException('Le nom est obligatoire');
        }

        // Règle 2 : Le prénom est obligatoire
        if (empty($user->getPrenom())) {
            throw new \InvalidArgumentException('Le prénom est obligatoire');
        }

        // Règle 3 : L'email doit être valide
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }

        // Règle 4 : Le mot de passe doit contenir au moins 8 caractères
        if (strlen($user->getMdp()) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caractères');
        }

        // Règle 5 : Le numéro de téléphone ne peut pas être négatif
        if ($user->getNum() < 0) {
            throw new \InvalidArgumentException('Le numéro de téléphone ne peut pas être négatif');
        }

        return true;
    }

    /**
     * Retourne le nom complet de l'utilisateur.
     */
    public function getFullName(Users $user): string
    {
        return $user->getFullName();
    }

    /**
     * Vérifie si l'utilisateur est actuellement bloqué.
     */
    public function isBlocked(Users $user): bool
    {
        return $user->getBlockUntil() > time();
    }

    /**
     * Vérifie si l'utilisateur a dépassé le nombre de tentatives autorisées.
     */
    public function hasExceededAttempts(Users $user, int $maxAttempts = 3): bool
    {
        return $user->getTentative() >= $maxAttempts;
    }
}
