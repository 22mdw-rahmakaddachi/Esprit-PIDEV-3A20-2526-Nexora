<?php

namespace App\Service;

use App\Entity\Activite;

class ActiviteManager
{
    /**
     * Valide les règles métier d'une activité.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Activite $activite): bool
    {
        // Règle 1 : Le nom est obligatoire (min 3 caractères)
        if (empty($activite->getNom())) {
            throw new \InvalidArgumentException('Le nom de l\'activité est obligatoire');
        }

        if (strlen($activite->getNom()) < 3) {
            throw new \InvalidArgumentException('Le nom doit contenir au moins 3 caractères');
        }

        // Règle 2 : Le type doit être valide
        $typesValides = ['Sport', 'Culture', 'Gastronomie', 'Aventure', 'Bien-être', 'Autre'];
        if (empty($activite->getType()) || !in_array($activite->getType(), $typesValides)) {
            throw new \InvalidArgumentException('Le type de l\'activité est invalide');
        }

        // Règle 3 : Le lieu est obligatoire
        if (empty($activite->getLieu())) {
            throw new \InvalidArgumentException('Le lieu est obligatoire');
        }

        // Règle 4 : Le prix doit être supérieur à 0
        if ($activite->getPrix() <= 0) {
            throw new \InvalidArgumentException('Le prix doit être supérieur à 0');
        }

        // Règle 5 : Le nombre de places doit être supérieur à 0
        if ($activite->getNombrePlaces() <= 0) {
            throw new \InvalidArgumentException('Le nombre de places doit être supérieur à 0');
        }

        // Règle 6 : Les places disponibles ne peuvent pas être négatives
        if ($activite->getPlacesDisponibles() < 0) {
            throw new \InvalidArgumentException('Les places disponibles ne peuvent pas être négatives');
        }

        // Règle 7 : Les places disponibles ne peuvent pas dépasser le nombre total de places
        if ($activite->getPlacesDisponibles() > $activite->getNombrePlaces()) {
            throw new \InvalidArgumentException('Les places disponibles ne peuvent pas dépasser le nombre total de places');
        }

        return true;
    }

    /**
     * Vérifie si l'activité est complète (plus de places disponibles).
     */
    public function isComplet(Activite $activite): bool
    {
        return $activite->getPlacesDisponibles() === 0;
    }

    /**
     * Calcule le taux de remplissage en pourcentage.
     */
    public function getTauxRemplissage(Activite $activite): float
    {
        if ($activite->getNombrePlaces() === 0) {
            return 0.0;
        }

        $placesOccupees = $activite->getNombrePlaces() - $activite->getPlacesDisponibles();
        return round(($placesOccupees / $activite->getNombrePlaces()) * 100, 1);
    }
}
