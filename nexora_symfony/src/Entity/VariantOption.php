<?php

namespace App\Entity;

use App\Repository\VariantOptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariantOptionRepository::class)]
#[ORM\Table(name: 'variant_option')]
class VariantOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProduitVariant::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProduitVariant $variant = null;

    #[ORM\ManyToOne(targetEntity: AttributVariation::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?AttributVariation $attribut = null;

    #[ORM\ManyToOne(targetEntity: OptionVariation::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?OptionVariation $optionVariation = null;

    public function getId(): ?int { return $this->id; }

    public function getVariant(): ?ProduitVariant { return $this->variant; }
    public function setVariant(?ProduitVariant $variant): static { $this->variant = $variant; return $this; }

    public function getAttribut(): ?AttributVariation { return $this->attribut; }
    public function setAttribut(?AttributVariation $attribut): static { $this->attribut = $attribut; return $this; }

    public function getOptionVariation(): ?OptionVariation { return $this->optionVariation; }
    public function setOptionVariation(?OptionVariation $optionVariation): static { $this->optionVariation = $optionVariation; return $this; }
}
