<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Table(name: 'offre')]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $titre = '';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $duree = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $date = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lien = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageLocal = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct() { $this->updatedAt = new \DateTime(); }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getPrix(): ?string { return $this->prix; }
    public function setPrix(?string $prix): static { $this->prix = $prix; return $this; }
    public function getPays(): ?string { return $this->pays; }
    public function setPays(?string $pays): static { $this->pays = $pays; return $this; }
    public function getDuree(): ?string { return $this->duree; }
    public function setDuree(?string $duree): static { $this->duree = $duree; return $this; }
    public function getDate(): ?string { return $this->date; }
    public function setDate(?string $date): static { $this->date = $date; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getLien(): ?string { return $this->lien; }
    public function setLien(?string $lien): static { $this->lien = $lien; return $this; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): static { $this->imageUrl = $imageUrl; return $this; }
    public function getImageLocal(): ?string { return $this->imageLocal; }
    public function setImageLocal(?string $imageLocal): static { $this->imageLocal = $imageLocal; return $this; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
