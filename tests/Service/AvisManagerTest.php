<?php

namespace App\Tests\Service;

use App\Entity\Avis;
use App\Service\AvisManager;
use PHPUnit\Framework\TestCase;

class AvisManagerTest extends TestCase
{
    private AvisManager $avisManager;

    protected function setUp(): void
    {
        $this->avisManager = new AvisManager();
    }

    // ─── Helper : crée un avis valide ───────────────────────────────────────

    private function makeValidAvis(): Avis
    {
        $avis = new Avis();
        $avis->setUserId(1);
        $avis->setTitre('Super activité');
        $avis->setContenu('J\'ai vraiment adoré cette expérience, je recommande vivement !');
        $avis->setRating(5);
        $avis->setCreatedAt(new \DateTime());

        return $avis;
    }

    // ─── Tests de validation : cas valide ───────────────────────────────────

    public function testValidAvis(): void
    {
        $avis = $this->makeValidAvis();

        $this->assertTrue($this->avisManager->validate($avis));
    }

    // ─── Tests de validation : titre ────────────────────────────────────────

    public function testAvisWithoutTitre(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre est obligatoire');

        $avis = $this->makeValidAvis();
        $avis->setTitre('');

        $this->avisManager->validate($avis);
    }

    public function testAvisWithTitreTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre doit contenir au moins 3 caractères');

        $avis = $this->makeValidAvis();
        $avis->setTitre('AB');

        $this->avisManager->validate($avis);
    }

    public function testAvisWithTitreTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre ne peut pas dépasser 100 caractères');

        $avis = $this->makeValidAvis();
        $avis->setTitre(str_repeat('A', 101));

        $this->avisManager->validate($avis);
    }

    // ─── Tests de validation : contenu ──────────────────────────────────────

    public function testAvisWithoutContenu(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire est obligatoire');

        $avis = $this->makeValidAvis();
        $avis->setContenu('');

        $this->avisManager->validate($avis);
    }

    public function testAvisWithContenuTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire doit contenir au moins 5 caractères');

        $avis = $this->makeValidAvis();
        $avis->setContenu('Ok');

        $this->avisManager->validate($avis);
    }

    public function testAvisWithContenuTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire ne peut pas dépasser 2000 caractères');

        $avis = $this->makeValidAvis();
        $avis->setContenu(str_repeat('A', 2001));

        $this->avisManager->validate($avis);
    }

    // ─── Tests de validation : note (rating) ────────────────────────────────

    public function testAvisWithRatingZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $avis = $this->makeValidAvis();
        $avis->setRating(0);

        $this->avisManager->validate($avis);
    }

    public function testAvisWithRatingAboveFive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $avis = $this->makeValidAvis();
        $avis->setRating(6);

        $this->avisManager->validate($avis);
    }

    public function testAvisWithRatingNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $avis = $this->makeValidAvis();
        $avis->setRating(-1);

        $this->avisManager->validate($avis);
    }

    // ─── Tests de validation : userId ───────────────────────────────────────

    public function testAvisWithInvalidUserId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'utilisateur est invalide');

        $avis = $this->makeValidAvis();
        $avis->setUserId(0);

        $this->avisManager->validate($avis);
    }

    // ─── Tests fonctionnels ─────────────────────────────────────────────────

    public function testGetSummary(): void
    {
        $avis = $this->makeValidAvis();
        $avis->setUserId(3);

        $this->assertSame('[5/5] Super activité — User #3', $this->avisManager->getSummary($avis));
    }

    public function testIsPositive(): void
    {
        $avis = $this->makeValidAvis();
        $avis->setRating(4);

        $this->assertTrue($this->avisManager->isPositive($avis));
    }

    public function testIsNotPositive(): void
    {
        $avis = $this->makeValidAvis();
        $avis->setRating(3);

        $this->assertFalse($this->avisManager->isPositive($avis));
    }

    public function testIsNegative(): void
    {
        $avis = $this->makeValidAvis();
        $avis->setRating(2);

        $this->assertTrue($this->avisManager->isNegative($avis));
    }

    public function testIsNotNegative(): void
    {
        $avis = $this->makeValidAvis();
        $avis->setRating(3);

        $this->assertFalse($this->avisManager->isNegative($avis));
    }
}
