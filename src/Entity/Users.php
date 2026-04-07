<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.')]
    private string $prenom = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')]
    private string $nom = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private string $email = '';

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'Le numéro est obligatoire.')]
    #[Assert\Positive(message: 'Le numéro doit être un nombre positif.')]
    private int $num = 0;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private string $mdp = '';

    #[ORM\Column(type: 'integer')]
    private int $tentative = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $validation = false;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
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

    public function getValidation(): bool
    {
        return $this->validation;
    }

    public function setValidation(bool $validation): static
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

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return [$this->role ?? 'ROLE_USER', 'ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return $this->mdp;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}
