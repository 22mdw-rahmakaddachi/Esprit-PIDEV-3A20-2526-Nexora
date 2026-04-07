<?php

namespace App\Entity;

use App\Repository\AttributVariationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributVariationRepository::class)]
#[ORM\Table(name: 'attribut_variation')]
class AttributVariation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $typeAffichage = null;

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

    public function getTypeAffichage(): ?string
    {
        return $this->typeAffichage;
    }

    public function setTypeAffichage(?string $typeAffichage): static
    {
        $this->typeAffichage = $typeAffichage;
        return $this;
    }

}
