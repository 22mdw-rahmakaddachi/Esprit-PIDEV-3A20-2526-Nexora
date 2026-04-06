<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $clientId = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $produitId = null;

    #[ORM\Column(nullable: true, length: 100)]
    private ?string $variantSku = null;

    #[ORM\Column(nullable: true, length: 200)]
    private ?string $produitNom = null;

    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?float $prixUnitaire = null;

    #[ORM\Column(type: 'integer')]
    private int $quantite = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateAjout = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): static
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getProduitId(): ?int
    {
        return $this->produitId;
    }

    public function setProduitId(?int $produitId): static
    {
        $this->produitId = $produitId;
        return $this;
    }

    public function getVariantSku(): ?string
    {
        return $this->variantSku;
    }

    public function setVariantSku(?string $variantSku): static
    {
        $this->variantSku = $variantSku;
        return $this;
    }

    public function getProduitNom(): ?string
    {
        return $this->produitNom;
    }

    public function setProduitNom(?string $produitNom): static
    {
        $this->produitNom = $produitNom;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(?\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

}
