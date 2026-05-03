<?php

namespace App\Tests\Service;

use App\Entity\ProduitParent;
use App\Service\ProduitManager;
use PHPUnit\Framework\TestCase;

class ProduitManagerTest extends TestCase
{
    private ProduitManager $produitManager;

    protected function setUp(): void
    {
        $this->produitManager = new ProduitManager();
    }

    // ─── Helper : crée un produit valide ────────────────────────────────────

    private function makeValidProduit(): ProduitParent
    {
        $produit = new ProduitParent();
        $produit->setNom('Sac de randonnée 40L');
        $produit->setDescription('Sac à dos idéal pour les randonnées de plusieurs jours avec compartiments multiples.');
        $produit->setDescriptionCourte('Sac de randonnée robuste et léger.');
        $produit->setMarque('Nexora Outdoor');
        $produit->setMateriau('Nylon 600D imperméable');
        $produit->setPoidsKg(1.2);
        $produit->setStatut('actif');
        $produit->setPartenaireId(5);
        $produit->setSousCategorieId(3);

        return $produit;
    }

    // ─── Tests de validation : cas valide ───────────────────────────────────

    public function testValidProduit(): void
    {
        $produit = $this->makeValidProduit();

        $this->assertTrue($this->produitManager->validate($produit));
    }

    // ─── Tests de validation : nom ──────────────────────────────────────────

    public function testProduitWithoutNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du produit est obligatoire');

        $produit = $this->makeValidProduit();
        $produit->setNom('');

        $this->produitManager->validate($produit);
    }

    public function testProduitWithNomTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom doit contenir au moins 3 caractères');

        $produit = $this->makeValidProduit();
        $produit->setNom('AB');

        $this->produitManager->validate($produit);
    }

    public function testProduitWithNomTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom ne peut pas dépasser 200 caractères');

        $produit = $this->makeValidProduit();
        $produit->setNom(str_repeat('A', 201));

        $this->produitManager->validate($produit);
    }

    // ─── Tests de validation : description ──────────────────────────────────

    public function testProduitWithoutDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La description complète est obligatoire');

        $produit = $this->makeValidProduit();
        $produit->setDescription(null);

        $this->produitManager->validate($produit);
    }

    public function testProduitWithEmptyDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La description complète est obligatoire');

        $produit = $this->makeValidProduit();
        $produit->setDescription('');

        $this->produitManager->validate($produit);
    }

    // ─── Tests de validation : marque ───────────────────────────────────────

    public function testProduitWithoutMarque(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La marque est obligatoire');

        $produit = $this->makeValidProduit();
        $produit->setMarque(null);

        $this->produitManager->validate($produit);
    }

    // ─── Tests de validation : poids ────────────────────────────────────────

    public function testProduitWithPoidsNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le poids doit être un nombre positif ou zéro');

        $produit = $this->makeValidProduit();
        $produit->setPoidsKg(-0.5);

        $this->produitManager->validate($produit);
    }

    public function testProduitWithPoidsZeroIsValid(): void
    {
        $produit = $this->makeValidProduit();
        $produit->setPoidsKg(0.0);

        $this->assertTrue($this->produitManager->validate($produit));
    }

    // ─── Tests de validation : statut ───────────────────────────────────────

    public function testProduitWithInvalidStatut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut doit être "actif" ou "inactif"');

        $produit = $this->makeValidProduit();
        $produit->setStatut('brouillon');

        $this->produitManager->validate($produit);
    }

    public function testProduitWithStatutInactifIsValid(): void
    {
        $produit = $this->makeValidProduit();
        $produit->setStatut('inactif');

        $this->assertTrue($this->produitManager->validate($produit));
    }

    // ─── Tests de validation : partenaireId ─────────────────────────────────

    public function testProduitWithPartenaireIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le partenaire est invalide');

        $produit = $this->makeValidProduit();
        $produit->setPartenaireId(0);

        $this->produitManager->validate($produit);
    }

    public function testProduitWithPartenaireIdNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le partenaire est invalide');

        $produit = $this->makeValidProduit();
        $produit->setPartenaireId(-1);

        $this->produitManager->validate($produit);
    }

    // ─── Tests fonctionnels ─────────────────────────────────────────────────

    public function testIsActif(): void
    {
        $produit = $this->makeValidProduit();
        $produit->setStatut('actif');

        $this->assertTrue($this->produitManager->isActif($produit));
    }

    public function testIsNotActif(): void
    {
        $produit = $this->makeValidProduit();
        $produit->setStatut('inactif');

        $this->assertFalse($this->produitManager->isActif($produit));
    }

    public function testHasNoVariants(): void
    {
        $produit = $this->makeValidProduit();

        $this->assertFalse($this->produitManager->hasVariants($produit));
    }

    public function testGetPrixMinWithNoVariants(): void
    {
        $produit = $this->makeValidProduit();

        $this->assertNull($this->produitManager->getPrixMin($produit));
    }
}
