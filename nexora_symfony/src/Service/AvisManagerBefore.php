<?php

namespace App\Service;

use App\Entity\Avis;

// VERSION AVANT CORRECTION — erreurs détectées par PHPStan

class AvisManagerBefore
{
    // Erreur 1 : retourne string au lieu de bool
    public function validate(Avis $avis): bool
    {
        if (empty($avis->getTitre())) {
            throw new \InvalidArgumentException('Titre obligatoire');
        }

        // Erreur 2 : variable non définie
        if ($noteCalculee > 5) {
            throw new \InvalidArgumentException('Note invalide');
        }

        return "valide";
    }

    // Erreur 3 : paramètre sans type
    public function isPositive($avis): bool
    {
        return $avis->getRating() >= 4;
    }

    // Erreur 4 : retourne null au lieu de string
    public function getSummary(Avis $avis): string
    {
        return null;
    }
}
