<?php

namespace App\Tests\Entity;

use App\Entity\ProduitParent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProduitParentValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function makeValid(): ProduitParent
    {
        $p = new ProduitParent();
        $p->setNom('Sac à dos randonnée');
        $p->setDescription('Un sac à dos léger et résistant pour la randonnée.');
        $p->setDescriptionCourte('Sac léger et résistant.');
        $p->setMarque('Quechua');
        $p->setMateriau('Nylon 600D');
        $p->setPoidsKg(1.5);
        $p->setStatut('actif');
        return $p;
    }

    // ── NOM ──────────────────────────────────────────────────────────────────

    public function testNomObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setNom('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testNomTropCourt(): void
    {
        $p = $this->makeValid();
        $p->setNom('AB');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('3 caractères', $errors[0]->getMessage());
    }

    public function testNomTropLong(): void
    {
        $p = $this->makeValid();
        $p->setNom(str_repeat('A', 201));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNomValide(): void
    {
        $p = $this->makeValid();
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── DESCRIPTION ───────────────────────────────────────────────────────────

    public function testDescriptionObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setDescription('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testDescriptionTropLongue(): void
    {
        $p = $this->makeValid();
        $p->setDescription(str_repeat('X', 2001));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testDescriptionValide(): void
    {
        $p = $this->makeValid();
        $p->setDescription('Un produit de qualité pour la randonnée.');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── DESCRIPTION COURTE ────────────────────────────────────────────────────

    public function testDescriptionCourteObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setDescriptionCourte('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testDescriptionCourteTropLongue(): void
    {
        $p = $this->makeValid();
        $p->setDescriptionCourte(str_repeat('X', 256));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testDescriptionCourteValide(): void
    {
        $p = $this->makeValid();
        $p->setDescriptionCourte('Sac léger et résistant.');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── MARQUE ────────────────────────────────────────────────────────────────

    public function testMarqueObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setMarque('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testMarqueTropLongue(): void
    {
        $p = $this->makeValid();
        $p->setMarque(str_repeat('M', 101));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testMarqueValide(): void
    {
        $p = $this->makeValid();
        $p->setMarque('Quechua');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── MATERIAU ──────────────────────────────────────────────────────────────

    public function testMateriauObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setMateriau('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testMateriauTropLong(): void
    {
        $p = $this->makeValid();
        $p->setMateriau(str_repeat('M', 501));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testMateriauValide(): void
    {
        $p = $this->makeValid();
        $p->setMateriau('Nylon 600D imperméable');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── POIDS ─────────────────────────────────────────────────────────────────

    public function testPoidsObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setPoidsKg(null);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testPoidsNegatif(): void
    {
        $p = $this->makeValid();
        $p->setPoidsKg(-1.5);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('positif', $errors[0]->getMessage());
    }

    public function testPoidsZeroValide(): void
    {
        $p = $this->makeValid();
        $p->setPoidsKg(0);
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    public function testPoidsValide(): void
    {
        $p = $this->makeValid();
        $p->setPoidsKg(2.5);
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── DIMENSIONS ────────────────────────────────────────────────────────────

    public function testDimensionsTropLongues(): void
    {
        $p = $this->makeValid();
        $p->setDimensionsCm(str_repeat('X', 51));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testDimensionsValides(): void
    {
        $p = $this->makeValid();
        $p->setDimensionsCm('60x30x20');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── STATUT ────────────────────────────────────────────────────────────────

    public function testStatutInvalide(): void
    {
        $p = $this->makeValid();
        $p->setStatut('brouillon');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('actif', $errors[0]->getMessage());
    }

    public function testStatutActif(): void
    {
        $p = $this->makeValid();
        $p->setStatut('actif');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    public function testStatutInactif(): void
    {
        $p = $this->makeValid();
        $p->setStatut('inactif');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }
}
