<?php

namespace App\Tests\Entity;

use App\Entity\CodePromo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CodePromoValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function makeValid(): CodePromo
    {
        $p = new CodePromo();
        $p->setCode('SUMMER20');
        $p->setTypeReduction('pourcentage');
        $p->setValeurReduction(20.0);
        $p->setDateDebut(new \DateTime('2026-01-01'));
        $p->setDateFin(new \DateTime('2026-12-31'));
        return $p;
    }

    // ── CODE ─────────────────────────────────────────────────────────────────

    public function testCodeObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setCode('');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testCodeTropCourt(): void
    {
        $p = $this->makeValid();
        $p->setCode('AB');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testCodeAvecCaracteresInvalides(): void
    {
        $p = $this->makeValid();
        $p->setCode('CODE PROMO!');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testCodeValide(): void
    {
        $p = $this->makeValid();
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── TYPE REDUCTION ───────────────────────────────────────────────────────

    public function testTypeReductionInvalide(): void
    {
        $p = $this->makeValid();
        $p->setTypeReduction('remise');
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testTypeReductionMontantFixe(): void
    {
        $p = $this->makeValid();
        $p->setTypeReduction('montant_fixe');
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── VALEUR REDUCTION ─────────────────────────────────────────────────────

    public function testValeurReductionNulle(): void
    {
        $p = $this->makeValid();
        $p->setValeurReduction(0);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('positive', $errors[0]->getMessage());
    }

    public function testValeurReductionNegative(): void
    {
        $p = $this->makeValid();
        $p->setValeurReduction(-10);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testValeurReductionValide(): void
    {
        $p = $this->makeValid();
        $p->setValeurReduction(15.5);
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── DATES ────────────────────────────────────────────────────────────────

    public function testDateDebutObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setDateDebut(null);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testDateFinObligatoire(): void
    {
        $p = $this->makeValid();
        $p->setDateFin(null);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testDateFinAvantDateDebut(): void
    {
        $p = $this->makeValid();
        $p->setDateDebut(new \DateTime('2026-12-31'));
        $p->setDateFin(new \DateTime('2026-01-01'));
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('postérieure', $errors[0]->getMessage());
    }

    // ── MONTANT MINIMUM ──────────────────────────────────────────────────────

    public function testMontantMinimumNegatif(): void
    {
        $p = $this->makeValid();
        $p->setMontantMinimum(-50.0);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testMontantMinimumValide(): void
    {
        $p = $this->makeValid();
        $p->setMontantMinimum(100.0);
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }

    // ── LIMITE UTILISATION ───────────────────────────────────────────────────

    public function testLimiteUtilisationNegative(): void
    {
        $p = $this->makeValid();
        $p->setLimiteUtilisation(-5);
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testLimiteUtilisationValide(): void
    {
        $p = $this->makeValid();
        $p->setLimiteUtilisation(100);
        $errors = $this->validator->validate($p);
        $this->assertCount(0, $errors);
    }
}
