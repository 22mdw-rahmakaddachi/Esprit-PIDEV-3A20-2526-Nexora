<?php

namespace App\Entity;

use App\Repository\CommandeItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeItemRepository::class)]
#[ORM\Table(name: 'commande_item')]
class CommandeItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $commandeId = 0;

    #[ORM\Column(length: 255)]
    private string $produitNom = '';

    #[ORM\Column(type: 'integer')]
    private int $quantite = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $prixUnitaire = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $sousTotal = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    public function setCommandeId(int $commandeId): static
    {
        $this->commandeId = $commandeId;
        return $this;
    }

    public function getProduitNom(): string
    {
        return $this->produitNom;
    }

    public function setProduitNom(string $produitNom): static
    {
        $this->produitNom = $produitNom;
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

    public function getPrixUnitaire(): float
    {
        return (float)$this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): static
    {
        $this->prixUnitaire = (string)$prixUnitaire;
        return $this;
    }

    public function getSousTotal(): float
    {
        return (float)$this->sousTotal;
    }

    public function setSousTotal(float $sousTotal): static
    {
        $this->sousTotal = (string)$sousTotal;
        return $this;
    }

}
