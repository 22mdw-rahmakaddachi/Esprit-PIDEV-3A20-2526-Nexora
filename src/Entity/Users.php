<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $prenom = '';

    #[ORM\Column(length: 50)]
    private string $nom = '';

    #[ORM\Column(length: 50)]
    private string $email = '';

    #[ORM\Column(type: 'integer')]
    private int $num = 0;

    #[ORM\Column(length: 50)]
    private string $role = '';

    #[ORM\Column(length: 255)]
    private string $mdp = '';

    #[ORM\Column(type: 'integer')]
    private int $tentative = 0;

    #[ORM\Column(type: 'boolean')]
    private int $validation = 0;

    #[ORM\Column(type: 'bigint')]
    private int $blockUntil = 0;

    #[ORM\Column(type: 'integer')]
    private int $blockLevel = 0;

    #[ORM\Column(nullable: true, length: 10)]
    private ?string $resetCode = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $resetExpiration = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fingerId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getNum(): int
    {
        return $this->num;
    }

    public function setNum(int $num): static
    {
        $this->num = $num;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): static
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getTentative(): int
    {
        return $this->tentative;
    }

    public function setTentative(int $tentative): static
    {
        $this->tentative = $tentative;
        return $this;
    }

    public function getValidation(): int
    {
        return $this->validation;
    }

    public function setValidation(int $validation): static
    {
        $this->validation = $validation;
        return $this;
    }

    public function getBlockUntil(): int
    {
        return $this->blockUntil;
    }

    public function setBlockUntil(int $blockUntil): static
    {
        $this->blockUntil = $blockUntil;
        return $this;
    }

    public function getBlockLevel(): int
    {
        return $this->blockLevel;
    }

    public function setBlockLevel(int $blockLevel): static
    {
        $this->blockLevel = $blockLevel;
        return $this;
    }

    public function getResetCode(): ?string
    {
        return $this->resetCode;
    }

    public function setResetCode(?string $resetCode): static
    {
        $this->resetCode = $resetCode;
        return $this;
    }

    public function getResetExpiration(): ?int
    {
        return $this->resetExpiration;
    }

    public function setResetExpiration(?int $resetExpiration): static
    {
        $this->resetExpiration = $resetExpiration;
        return $this;
    }

    public function getFingerId(): ?int
    {
        return $this->fingerId;
    }

    public function setFingerId(?int $fingerId): static
    {
        $this->fingerId = $fingerId;
        return $this;
    }

}
