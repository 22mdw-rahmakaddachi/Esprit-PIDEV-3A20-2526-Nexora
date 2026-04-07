<?php

namespace App\Entity;

use App\Repository\ProduitVariantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitVariantRepository::class)]
#[ORM\Table(name: 'produit_variant')]
class ProduitVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $produitParentId = 0;

    #[ORM\Column(length: 100)]
    private string $sku = '';

    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?float $prixAchat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $prixVente = 0.0;

    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?float $prixPromo = null;

    #[ORM\Column(type: 'integer')]
    private int $quantiteStock = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $seuilAlerte = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $imageSpecifique = null;

    #[ORM\Column(nullable: true, length: 50)]
    private ?string $codeBarres = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduitParentId(): int
    {
        return $this->produitParentId;
    }

    public function setProduitParentId(int $produitParentId): static
    {
        $this->produitParentId = $produitParentId;
        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;
        return $this;
    }

    public function getPrixVente(): float
    {
        return $this->prixVente;
    }

    public function setPrixVente(float $prixVente): static
    {
        $this->prixVente = $prixVente;
        return $this;
    }

    public function getPrixPromo(): ?float
    {
        return $this->prixPromo;
    }

    public function setPrixPromo(?float $prixPromo): static
    {
        $this->prixPromo = $prixPromo;
        return $this;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;
        return $this;
    }

    public function getSeuilAlerte(): ?int
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(?int $seuilAlerte): static
    {
        $this->seuilAlerte = $seuilAlerte;
        return $this;
    }

    public function getImageSpecifique(): ?string
    {
        return $this->imageSpecifique;
    }

    public function setImageSpecifique(?string $imageSpecifique): static
    {
        $this->imageSpecifique = $imageSpecifique;
        return $this;
    }

    public function getCodeBarres(): ?string
    {
        return $this->codeBarres;
    }

    public function setCodeBarres(?string $codeBarres): static
    {
        $this->codeBarres = $codeBarres;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

}
