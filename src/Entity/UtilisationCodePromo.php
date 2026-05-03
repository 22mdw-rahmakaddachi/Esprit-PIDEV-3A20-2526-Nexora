<?php

namespace App\Entity;

use App\Repository\UtilisationCodePromoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisationCodePromoRepository::class)]
#[ORM\Table(name: 'utilisation_code_promo')]
class UtilisationCodePromo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $codePromoId = 0;

    #[ORM\Column(type: 'integer')]
    private int $clientId = 0;

    #[ORM\Column(type: 'integer')]
    private int $commandeId = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montantReduction = '0.00';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateUtilisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodePromoId(): int
    {
        return $this->codePromoId;
    }

    public function setCodePromoId(int $codePromoId): static
    {
        $this->codePromoId = $codePromoId;
        return $this;
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

    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    public function setCommandeId(int $commandeId): static
    {
        $this->commandeId = $commandeId;
        return $this;
    }

    public function getMontantReduction(): float
    {
        return (float)$this->montantReduction;
    }

    public function setMontantReduction(float $montantReduction): static
    {
        $this->montantReduction = (string)$montantReduction;
        return $this;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(?\DateTimeInterface $dateUtilisation): static
    {
        $this->dateUtilisation = $dateUtilisation;
        return $this;
    }

}
