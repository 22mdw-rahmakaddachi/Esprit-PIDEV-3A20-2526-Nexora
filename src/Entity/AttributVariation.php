<?php

namespace App\Entity;

use App\Repository\AttributVariationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(targetEntity: OptionVariation::class, mappedBy: 'attribut', cascade: ['persist', 'remove'])]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getTypeAffichage(): ?string { return $this->typeAffichage; }
    public function setTypeAffichage(?string $typeAffichage): static { $this->typeAffichage = $typeAffichage; return $this; }

    public function getOptions(): Collection { return $this->options; }
    public function addOption(OptionVariation $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setAttribut($this);
        }
        return $this;
    }
    public function removeOption(OptionVariation $option): static { $this->options->removeElement($option); return $this; }
}
