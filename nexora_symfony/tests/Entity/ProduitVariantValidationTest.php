<?php

namespace App\Tests\Entity;

use App\Entity\ProduitVariant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProduitVariantValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function makeValid(): ProduitVariant
    {
        $v = new ProduitVariant();
        $v->setSku('SAC-L-ROUGE');
        $v->setPrixVente(49.99);
        $v->setQuantiteStock(10);
        return $v;
    }

    // ── SKU ───────────────────────────────────────────────────────────────────

    public function testSkuObligatoire(): void
    {
        $v = $this->makeValid();
        $v->setSku('');
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }

    public function testSkuTropCourt(): void
    {
        $v = $this->makeValid();
        $v->setSku('A');
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testSkuAvecCaracteresInvalides(): void
    {
        $v = $this->makeValid();
        $v->setSku('SKU INVALIDE!');
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testSkuValide(): void
    {
        $v = $this->makeValid();
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    // ── PRIX VENTE ────────────────────────────────────────────────────────────

    public function testPrixVenteObligatoire(): void
    {
        $v = $this->makeValid();
        $v->setPrixVente(0);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('supérieur à zéro', $errors[0]->getMessage());
    }

    public function testPrixVenteNegatif(): void
    {
        $v = $this->makeValid();
        $v->setPrixVente(-10.0);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testPrixVenteValide(): void
    {
        $v = $this->makeValid();
        $v->setPrixVente(29.99);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    // ── PRIX PROMO ────────────────────────────────────────────────────────────

    public function testPrixPromoNegatif(): void
    {
        $v = $this->makeValid();
        $v->setPrixPromo(-5.0);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testPrixPromoValide(): void
    {
        $v = $this->makeValid();
        $v->setPrixPromo(39.99);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    public function testPrixPromoNull(): void
    {
        $v = $this->makeValid();
        $v->setPrixPromo(null);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    // ── STOCK ─────────────────────────────────────────────────────────────────

    public function testStockNegatif(): void
    {
        $v = $this->makeValid();
        $v->setQuantiteStock(-1);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('positif', $errors[0]->getMessage());
    }

    public function testStockZeroValide(): void
    {
        $v = $this->makeValid();
        $v->setQuantiteStock(0);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    public function testStockValide(): void
    {
        $v = $this->makeValid();
        $v->setQuantiteStock(50);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    // ── SEUIL ALERTE ──────────────────────────────────────────────────────────

    public function testSeuilAlerteNegatif(): void
    {
        $v = $this->makeValid();
        $v->setSeuilAlerte(-3);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testSeuilAlerteValide(): void
    {
        $v = $this->makeValid();
        $v->setSeuilAlerte(5);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }

    // ── PRIX ACHAT ────────────────────────────────────────────────────────────

    public function testPrixAchatNegatif(): void
    {
        $v = $this->makeValid();
        $v->setPrixAchat(-20.0);
        $errors = $this->validator->validate($v);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testPrixAchatValide(): void
    {
        $v = $this->makeValid();
        $v->setPrixAchat(25.0);
        $errors = $this->validator->validate($v);
        $this->assertCount(0, $errors);
    }
}
