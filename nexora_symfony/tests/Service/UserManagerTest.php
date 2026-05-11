<?php

namespace App\Tests\Service;

use App\Entity\Users;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->userManager = new UserManager();
    }

    // ─── Tests de validation ────────────────────────────────────────────────

    public function testValidUser(): void
    {
        $user = new Users();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean.dupont@example.com');
        $user->setMdp('motdepasse123');
        $user->setNum(12345678);

        $this->assertTrue($this->userManager->validate($user));
    }

    public function testUserWithoutNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire');

        $user = new Users();
        $user->setPrenom('Jean');
        $user->setEmail('jean@example.com');
        $user->setMdp('motdepasse123');
        $user->setNum(12345678);

        $this->userManager->validate($user);
    }

    public function testUserWithoutPrenom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire');

        $user = new Users();
        $user->setNom('Dupont');
        $user->setEmail('dupont@example.com');
        $user->setMdp('motdepasse123');
        $user->setNum(12345678);

        $this->userManager->validate($user);
    }

    public function testUserWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email invalide');

        $user = new Users();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('email_invalide');
        $user->setMdp('motdepasse123');
        $user->setNum(12345678);

        $this->userManager->validate($user);
    }

    public function testUserWithShortPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le mot de passe doit contenir au moins 8 caractères');

        $user = new Users();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean.dupont@example.com');
        $user->setMdp('court');
        $user->setNum(12345678);

        $this->userManager->validate($user);
    }

    public function testUserWithNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numéro de téléphone ne peut pas être négatif');

        $user = new Users();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean.dupont@example.com');
        $user->setMdp('motdepasse123');
        $user->setNum(-1);

        $this->userManager->validate($user);
    }

    // ─── Tests fonctionnels ─────────────────────────────────────────────────

    public function testGetFullName(): void
    {
        $user = new Users();
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        $this->assertSame('Jean Dupont', $this->userManager->getFullName($user));
    }

    public function testUserNotBlocked(): void
    {
        $user = new Users();
        $user->setBlockUntil(0);

        $this->assertFalse($this->userManager->isBlocked($user));
    }

    public function testUserIsBlocked(): void
    {
        $user = new Users();
        $user->setBlockUntil(time() + 3600);

        $this->assertTrue($this->userManager->isBlocked($user));
    }

    public function testUserHasExceededAttempts(): void
    {
        $user = new Users();
        $user->setTentative(5);

        $this->assertTrue($this->userManager->hasExceededAttempts($user, 3));
    }

    public function testUserHasNotExceededAttempts(): void
    {
        $user = new Users();
        $user->setTentative(2);

        $this->assertFalse($this->userManager->hasExceededAttempts($user, 3));
    }
}
