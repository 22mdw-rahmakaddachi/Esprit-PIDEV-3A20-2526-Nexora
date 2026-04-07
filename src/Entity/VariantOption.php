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

    #[ORM\ManyToOne(targetEntity: ProduitVariant::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProduitVariant $variant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariant(): ?ProduitVariant
    {
        return $this->variant;
    }

    public function setVariant(?ProduitVariant $variant): static
    {
        $this->variant = $variant;
        return $this;
    }
}
