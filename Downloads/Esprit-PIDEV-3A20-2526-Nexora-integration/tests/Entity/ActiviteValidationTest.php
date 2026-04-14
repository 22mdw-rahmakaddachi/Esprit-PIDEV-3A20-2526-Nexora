<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ActiviteValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function makeValid(): Activite
    {
        $a = new Activite();
        $a->setNom('Randonnée Zaghouan');
        $a->setType('Sport');
        $a->setGenreCible('MIXTE');
        $a->setLieu('Tunis');
        $a->setDescription('Une belle randonnée.');
        $a->setPrix(25.0);
        $a->setNombrePlaces(10);
        $a->setPlacesDisponibles(10);
        $a->setAvecDate(false);
        return $a;
    }

    /** Filtre les erreurs sur 'partenaire' (nécessite DB) */
    private function erreursSansPartenaire(ConstraintViolationListInterface $errors): array
    {
        return array_filter(
            iterator_to_array($errors),
            fn($e) => $e->getPropertyPath() !== 'partenaire'
        );
    }

    // ── NOM ──

    public function testNomObligatoire(): void
    {
        $a = $this->makeValid();
        $a->setNom(null);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Nom null doit générer une erreur');
        $msg = array_values($errors)[0]->getMessage();
        $this->assertStringContainsString('obligatoire', $msg);
    }

    public function testNomTropCourt(): void
    {
        $a = $this->makeValid();
        $a->setNom('ab');
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Nom trop court doit générer une erreur');
    }

    public function testNomValide(): void
    {
        $a = $this->makeValid();
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors, 'Activité valide ne doit pas avoir d\'erreurs');
    }

    // ── TYPE ──

    public function testTypeObligatoire(): void
    {
        $a = $this->makeValid();
        $a->setType(null);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Type null doit générer une erreur');
    }

    public function testTypeInvalide(): void
    {
        $a = $this->makeValid();
        $a->setType('TypeInexistant');
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Type invalide doit générer une erreur');
    }

    public function testTypeValide(): void
    {
        foreach (['Sport', 'Culture', 'Gastronomie', 'Aventure', 'Bien-être', 'Autre'] as $type) {
            $a = $this->makeValid();
            $a->setType($type);
            $errors = $this->erreursSansPartenaire($this->validator->validate($a));
            $this->assertEmpty($errors, "Type '$type' devrait être valide");
        }
    }

    // ── GENRE CIBLE ──

    public function testGenreCibleObligatoire(): void
    {
        $a = $this->makeValid();
        $a->setGenreCible(null);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'GenreCible null doit générer une erreur');
    }

    public function testGenreCibleInvalide(): void
    {
        $a = $this->makeValid();
        $a->setGenreCible('INCONNU');
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'GenreCible invalide doit générer une erreur');
    }

    public function testGenreCibleValide(): void
    {
        foreach (['MIXTE', 'MASCULIN', 'FEMININ'] as $genre) {
            $a = $this->makeValid();
            $a->setGenreCible($genre);
            $errors = $this->erreursSansPartenaire($this->validator->validate($a));
            $this->assertEmpty($errors, "Genre '$genre' devrait être valide");
        }
    }

    // ── LIEU ──

    public function testLieuObligatoire(): void
    {
        $a = $this->makeValid();
        $a->setLieu(null);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Lieu null doit générer une erreur');
    }

    public function testLieuValide(): void
    {
        $a = $this->makeValid();
        $a->setLieu('Sousse');
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors, 'Lieu valide ne doit pas générer d\'erreur');
    }

    // ── PRIX ──

    public function testPrixNegatifInvalide(): void
    {
        $a = $this->makeValid();
        $a->setPrix(-5.0);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Prix négatif doit générer une erreur');
    }

    public function testPrixZeroInvalide(): void
    {
        $a = $this->makeValid();
        $a->setPrix(0);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Prix zéro doit générer une erreur');
    }

    public function testPrixValide(): void
    {
        $a = $this->makeValid();
        $a->setPrix(50.0);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors, 'Prix valide ne doit pas générer d\'erreur');
    }

    // ── NOMBRE DE PLACES ──

    public function testNombrePlacesZeroInvalide(): void
    {
        $a = $this->makeValid();
        $a->setNombrePlaces(0);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'NombrePlaces=0 doit générer une erreur');
    }

    public function testNombrePlacesNegatifInvalide(): void
    {
        $a = $this->makeValid();
        $a->setNombrePlaces(-1);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'NombrePlaces négatif doit générer une erreur');
    }

    public function testNombrePlacesValide(): void
    {
        $a = $this->makeValid();
        $a->setNombrePlaces(20);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors, 'NombrePlaces valide ne doit pas générer d\'erreur');
    }

    // ── DESCRIPTION ──

    public function testDescriptionTropLongue(): void
    {
        $a = $this->makeValid();
        $a->setDescription(str_repeat('x', 2001));
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'Description trop longue doit générer une erreur');
    }

    public function testDescriptionValide(): void
    {
        $a = $this->makeValid();
        $a->setDescription('Description correcte.');
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors);
    }

    // ── PLACES DISPONIBLES ──

    public function testPlacesDisponiblesNegativesInvalide(): void
    {
        $a = $this->makeValid();
        $a->setPlacesDisponibles(-1);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertNotEmpty($errors, 'PlacesDisponibles négatives doit générer une erreur');
    }

    public function testPlacesDisponiblesValide(): void
    {
        $a = $this->makeValid();
        $a->setPlacesDisponibles(5);
        $errors = $this->erreursSansPartenaire($this->validator->validate($a));
        $this->assertEmpty($errors);
    }
}
