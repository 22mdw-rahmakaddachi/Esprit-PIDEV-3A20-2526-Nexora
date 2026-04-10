<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom de la catégorie est obligatoire.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(length: 100)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ordreAffichage = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getOrdreAffichage(): ?int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(?int $ordreAffichage): static
    {
        $this->ordreAffichage = $ordreAffichage;
        return $this;
    }

}
