<?php

namespace App\Entity;

use App\Repository\SousCategorieRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SousCategorieRepository::class)]
#[ORM\Table(name: 'sous_categorie')]
class SousCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $categorieId = 0;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id', nullable: true)]
    private ?Categorie $categorie = null;

    #[Assert\NotBlank(message: 'Le nom de la sous-catégorie est obligatoire.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(length: 100)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $image = null;

    public function getId(): ?int { return $this->id; }

    public function getCategorieId(): int { return $this->categorieId; }
    public function setCategorieId(int $categorieId): static { $this->categorieId = $categorieId; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
        if ($categorie) $this->categorieId = $categorie->getId() ?? 0;
        return $this;
    }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }
}
