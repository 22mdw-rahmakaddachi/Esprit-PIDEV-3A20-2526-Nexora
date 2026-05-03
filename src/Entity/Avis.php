<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId = 0;

    #[ORM\Column(name: 'activite_id', type: 'integer', nullable: true)]
    private ?int $activiteId = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre 1 et 5.')]
    #[Assert\NotBlank(message: 'La note est obligatoire.')]
    private int $rating = 0;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private string $titre = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le commentaire est obligatoire.')]
    #[Assert\Length(
        min: 5,
        max: 2000,
        minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
    )]
    private string $contenu = '';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // ── Compatibility aliases used by base project templates ──

    public function getId(): ?int { return $this->id; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): static { $this->userId = $userId; return $this; }

    public function getActiviteId(): ?int { return $this->activiteId; }
    public function setActiviteId(?int $activiteId): static { $this->activiteId = $activiteId; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): static { $this->rating = $rating; return $this; }

    /** Alias: note = rating */
    public function getNote(): int { return $this->rating; }
    public function setNote(int $note): static { $this->rating = $note; return $this; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getContenu(): string { return $this->contenu; }
    public function setContenu(string $contenu): static { $this->contenu = trim($contenu); return $this; }

    /** Alias: commentaire = contenu */
    public function getCommentaire(): string { return $this->contenu; }
    public function setCommentaire(string $commentaire): static { $this->contenu = trim($commentaire); return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** Alias: auteur — stored as userId, display as "User #id" */
    public function getAuteur(): string { return 'User #' . $this->userId; }
    public function setAuteur(string $auteur): static { return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }
}
