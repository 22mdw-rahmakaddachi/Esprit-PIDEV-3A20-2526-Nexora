<?php

namespace App\Tests\Service;

use App\Entity\Commentaire;
use App\Service\CommentaireManager;
use PHPUnit\Framework\TestCase;

class CommentaireManagerTest extends TestCase
{
    private CommentaireManager $commentaireManager;

    protected function setUp(): void
    {
        $this->commentaireManager = new CommentaireManager();
    }

    // ─── Helper : crée un commentaire valide ────────────────────────────────

    private function makeValidCommentaire(): Commentaire
    {
        $commentaire = new Commentaire();
        $commentaire->setUserId(1);
        $commentaire->setAvisId(10);
        $commentaire->setContenu('Très bon avis, je partage ton opinion !');
        $commentaire->setCreatedAt(new \DateTime());

        return $commentaire;
    }

    // ─── Tests de validation : cas valide ───────────────────────────────────

    public function testValidCommentaire(): void
    {
        $commentaire = $this->makeValidCommentaire();

        $this->assertTrue($this->commentaireManager->validate($commentaire));
    }

    // ─── Tests de validation : contenu ──────────────────────────────────────

    public function testCommentaireWithoutContenu(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire est obligatoire');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setContenu('');

        $this->commentaireManager->validate($commentaire);
    }

    public function testCommentaireWithContenuTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire doit contenir au moins 2 caractères');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setContenu('A');

        $this->commentaireManager->validate($commentaire);
    }

    public function testCommentaireWithContenuTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire ne peut pas dépasser 1000 caractères');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setContenu(str_repeat('A', 1001));

        $this->commentaireManager->validate($commentaire);
    }

    // ─── Tests de validation : avisId ───────────────────────────────────────

    public function testCommentaireWithAvisIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'identifiant de l\'avis doit être positif');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setAvisId(0);

        $this->commentaireManager->validate($commentaire);
    }

    public function testCommentaireWithAvisIdNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'identifiant de l\'avis doit être positif');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setAvisId(-5);

        $this->commentaireManager->validate($commentaire);
    }

    // ─── Tests de validation : userId ───────────────────────────────────────

    public function testCommentaireWithUserIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'utilisateur est invalide');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setUserId(0);

        $this->commentaireManager->validate($commentaire);
    }

    public function testCommentaireWithUserIdNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'utilisateur est invalide');

        $commentaire = $this->makeValidCommentaire();
        $commentaire->setUserId(-1);

        $this->commentaireManager->validate($commentaire);
    }

    // ─── Tests fonctionnels ─────────────────────────────────────────────────

    public function testGetPreviewShortContenu(): void
    {
        $commentaire = $this->makeValidCommentaire();
        $commentaire->setContenu('Très bon avis !');

        $this->assertSame('Très bon avis !', $this->commentaireManager->getPreview($commentaire));
    }

    public function testGetPreviewLongContenu(): void
    {
        $commentaire = $this->makeValidCommentaire();
        $commentaire->setContenu(str_repeat('A', 80));

        $preview = $this->commentaireManager->getPreview($commentaire);

        $this->assertSame(str_repeat('A', 50) . '...', $preview);
    }

    public function testIsOwnedByCorrectUser(): void
    {
        $commentaire = $this->makeValidCommentaire();
        $commentaire->setUserId(7);

        $this->assertTrue($this->commentaireManager->isOwnedBy($commentaire, 7));
    }

    public function testIsNotOwnedByOtherUser(): void
    {
        $commentaire = $this->makeValidCommentaire();
        $commentaire->setUserId(7);

        $this->assertFalse($this->commentaireManager->isOwnedBy($commentaire, 99));
    }
}
