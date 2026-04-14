<?php

namespace App\Entity;

use App\Repository\OptionVariationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionVariationRepository::class)]
#[ORM\Table(name: 'option_variation')]
class OptionVariation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $attributId = 0;

    #[ORM\ManyToOne(targetEntity: AttributVariation::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'attribut_id', referencedColumnName: 'id', nullable: true)]
    private ?AttributVariation $attribut = null;

    #[ORM\Column(length: 100)]
    private string $valeur = '';

    #[ORM\Column(nullable: true, length: 7)]
    private ?string $codeHexadecimal = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ordreAffichage = null;

    public function getId(): ?int { return $this->id; }

    public function getAttributId(): int { return $this->attributId; }
    public function setAttributId(int $attributId): static { $this->attributId = $attributId; return $this; }

    public function getAttribut(): ?AttributVariation { return $this->attribut; }
    public function setAttribut(?AttributVariation $attribut): static
    {
        $this->attribut = $attribut;
        if ($attribut) $this->attributId = $attribut->getId() ?? 0;
        return $this;
    }

    public function getValeur(): string { return $this->valeur; }
    public function setValeur(string $valeur): static { $this->valeur = $valeur; return $this; }

    public function getCodeHexadecimal(): ?string { return $this->codeHexadecimal; }
    public function setCodeHexadecimal(?string $codeHexadecimal): static { $this->codeHexadecimal = $codeHexadecimal; return $this; }

    public function getOrdreAffichage(): ?int { return $this->ordreAffichage; }
    public function setOrdreAffichage(?int $ordreAffichage): static { $this->ordreAffichage = $ordreAffichage; return $this; }
}
