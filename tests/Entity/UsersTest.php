<?php

namespace App\Tests\Entity;

use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    public function testGetFullName(): void
    {
        $user = new Users();
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        $this->assertEquals('Jean Dupont', $user->getFullName());
    }

    public function testGetRolesWithAdminRole(): void
    {
        $user = new Users();
        $user->setRole('admin');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesWithParticipantRole(): void
    {
        $user = new Users();
        $user->setRole('role_participant');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_PARTICIPANT', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesWithPartenaireRole(): void
    {
        $user = new Users();
        $user->setRole('partner');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_PARTENAIRE', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesDefault(): void
    {
        $user = new Users();
        $user->setRole('random_role');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        // Should only contain ROLE_USER
        $this->assertCount(1, $roles);
    }

    public function testGetUserIdentifier(): void
    {
        $user = new Users();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }
    
    public function testGettersAndSetters(): void
    {
        $user = new Users();
        
        $user->setTentative(3);
        $this->assertEquals(3, $user->getTentative());
        
        $user->setValidation(1);
        $this->assertEquals(1, $user->getValidation());
        
        $user->setBlockLevel(2);
        $this->assertEquals(2, $user->getBlockLevel());
    }
}
