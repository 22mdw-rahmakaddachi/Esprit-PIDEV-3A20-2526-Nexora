<?php

namespace App\Tests\Service;

use App\Entity\Activite;
use App\Service\ActiviteManager;
use PHPUnit\Framework\TestCase;

class ActiviteManagerTest extends TestCase
{
    private ActiviteManager $activiteManager;

    protected function setUp(): void
    {
        $this->activiteManager = new ActiviteManager();
    }

    // ─── Helper : crée une activité valide ──────────────────────────────────

    private function makeValidActivite(): Activite
    {
        $activite = new Activite();
        $activite->setNom('Randonnée en montagne');
        $activite->setType('Sport');
        $activite->setLieu('Ain Draham');
        $activite->setPrix(49.90);
        $activite->setNombrePlaces(20);
        $activite->setPlacesDisponibles(15);
        $activite->setGenreCible('MIXTE');

        return $activite;
    }

    // ─── Tests de validation : cas valide ───────────────────────────────────

    public function testValidActivite(): void
    {
        $activite = $this->makeValidActivite();

        $this->assertTrue($this->activiteManager->validate($activite));
    }

    // ─── Tests de validation : nom ──────────────────────────────────────────

    public function testActiviteWithoutNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de l\'activité est obligatoire');

        $activite = $this->makeValidActivite();
        $activite->setNom('');

        $this->activiteManager->validate($activite);
    }

    public function testActiviteWithNomTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom doit contenir au moins 3 caractères');

        $activite = $this->makeValidActivite();
        $activite->setNom('AB');

        $this->activiteManager->validate($activite);
    }

    // ─── Tests de validation : type ─────────────────────────────────────────

    public function testActiviteWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type de l\'activité est invalide');

        $activite = $this->makeValidActivite();
        $activite->setType('TypeInexistant');

        $this->activiteManager->validate($activite);
    }

    public function testActiviteWithEmptyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type de l\'activité est invalide');

        $activite = $this->makeValidActivite();
        $activite->setType('');

        $this->activiteManager->validate($activite);
    }

    // ─── Tests de validation : lieu ─────────────────────────────────────────

    public function testActiviteWithoutLieu(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le lieu est obligatoire');

        $activite = $this->makeValidActivite();
        $activite->setLieu('');

        $this->activiteManager->validate($activite);
    }

    // ─── Tests de validation : prix ─────────────────────────────────────────

    public function testActiviteWithPrixZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prix doit être supérieur à 0');

        $activite = $this->makeValidActivite();
        $activite->setPrix(0);

        $this->activiteManager->validate($activite);
    }

    public function testActiviteWithPrixNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prix doit être supérieur à 0');

        $activite = $this->makeValidActivite();
        $activite->setPrix(-10.0);

        $this->activiteManager->validate($activite);
    }

    // ─── Tests de validation : places ───────────────────────────────────────

    public function testActiviteWithNombrePlacesZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre de places doit être supérieur à 0');

        $activite = $this->makeValidActivite();
        $activite->setNombrePlaces(0);

        $this->activiteManager->validate($activite);
    }

    public function testActiviteWithPlacesDisponiblesNegatives(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les places disponibles ne peuvent pas être négatives');

        $activite = $this->makeValidActivite();
        $activite->setPlacesDisponibles(-1);

        $this->activiteManager->validate($activite);
    }

    public function testActiviteWithPlacesDisponiblesSupNombrePlaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les places disponibles ne peuvent pas dépasser le nombre total de places');

        $activite = $this->makeValidActivite();
        $activite->setNombrePlaces(10);
        $activite->setPlacesDisponibles(15);

        $this->activiteManager->validate($activite);
    }

    // ─── Tests fonctionnels ─────────────────────────────────────────────────

    public function testIsCompletWhenNoPlaces(): void
    {
        $activite = $this->makeValidActivite();
        $activite->setPlacesDisponibles(0);

        $this->assertTrue($this->activiteManager->isComplet($activite));
    }

    public function testIsNotCompletWhenPlacesAvailable(): void
    {
        $activite = $this->makeValidActivite();
        $activite->setPlacesDisponibles(5);

        $this->assertFalse($this->activiteManager->isComplet($activite));
    }

    public function testGetTauxRemplissage(): void
    {
        $activite = $this->makeValidActivite();
        $activite->setNombrePlaces(20);
        $activite->setPlacesDisponibles(5); // 15 occupées sur 20

        $this->assertSame(75.0, $this->activiteManager->getTauxRemplissage($activite));
    }

    public function testGetTauxRemplissageWhenFull(): void
    {
        $activite = $this->makeValidActivite();
        $activite->setNombrePlaces(10);
        $activite->setPlacesDisponibles(0);

        $this->assertSame(100.0, $this->activiteManager->getTauxRemplissage($activite));
    }

    public function testGetTauxRemplissageWhenEmpty(): void
    {
        $activite = $this->makeValidActivite();
        $activite->setNombrePlaces(10);
        $activite->setPlacesDisponibles(10);

        $this->assertSame(0.0, $this->activiteManager->getTauxRemplissage($activite));
    }
}
