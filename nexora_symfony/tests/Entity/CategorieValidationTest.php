<?php

namespace App\Tests\Entity;

use App\Entity\Categorie;
use App\Entity\SousCategorie;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CategorieValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    // ── CATEGORIE ─────────────────────────────────────────────────────────────

    public function testCategorieNomObligatoire(): void
    {
        $cat = new Categorie();
        $cat->setNom('');
        $errors = $this->validator->validate($cat);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testCategorieNomTropCourt(): void
    {
        $cat = new Categorie();
        $cat->setNom('A');
        $errors = $this->validator->validate($cat);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testCategorieNomTropLong(): void
    {
        $cat = new Categorie();
        $cat->setNom(str_repeat('X', 101));
        $errors = $this->validator->validate($cat);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testCategorieValide(): void
    {
        $cat = new Categorie();
        $cat->setNom('Randonnée');
        $errors = $this->validator->validate($cat);
        $this->assertCount(0, $errors);
    }

    // ── SOUS-CATEGORIE ────────────────────────────────────────────────────────

    public function testSousCategorieNomObligatoire(): void
    {
        $sc = new SousCategorie();
        $sc->setNom('');
        $errors = $this->validator->validate($sc);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testSousCategorieNomTropCourt(): void
    {
        $sc = new SousCategorie();
        $sc->setNom('A');
        $errors = $this->validator->validate($sc);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testSousCategorieNomTropLong(): void
    {
        $sc = new SousCategorie();
        $sc->setNom(str_repeat('X', 101));
        $errors = $this->validator->validate($sc);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testSousCategorieValide(): void
    {
        $sc = new SousCategorie();
        $sc->setNom('Chaussures de randonnée');
        $errors = $this->validator->validate($sc);
        $this->assertCount(0, $errors);
    }
}
