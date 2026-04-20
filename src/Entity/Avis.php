<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: 'integer')]
    private int $rating = 0;

    #[ORM\Column(length: 100)]
    private string $titre = '';

    #[ORM\Column(type: 'text')]
    private string $contenu = '';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    // ── Compatibility aliases used by base project templates ──

    public function getId(): ?int { return $this->id; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): static { $this->userId = $userId; return $this; }

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

    /** Not in DB — kept for template compatibility */
    public function getActiviteId(): int { return 0; }
    public function setActiviteId(int $activiteId): static { return $this; }

    public function getActiviteNom(): ?string { return null; }
    public function setActiviteNom(?string $activiteNom): static { return $this; }

    public function getImage(): ?string { return null; }
    public function setImage(?string $image): static { return $this; }
}
