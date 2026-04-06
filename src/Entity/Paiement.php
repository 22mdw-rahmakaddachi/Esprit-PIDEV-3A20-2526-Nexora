<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiement')]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $commandeId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $demandeId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $clientId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $activiteId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $montant = 0.0;

    #[ORM\Column(length: 100)]
    private string $methodePaiement = '';

    #[ORM\Column(nullable: true, length: 50)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $transactionId = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $referenceExterne = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $referenceTransaction = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $detailsJson = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeId(): ?int
    {
        return $this->commandeId;
    }

    public function setCommandeId(?int $commandeId): static
    {
        $this->commandeId = $commandeId;
        return $this;
    }

    public function getDemandeId(): ?int
    {
        return $this->demandeId;
    }

    public function setDemandeId(?int $demandeId): static
    {
        $this->demandeId = $demandeId;
        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): static
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getActiviteId(): ?int
    {
        return $this->activiteId;
    }

    public function setActiviteId(?int $activiteId): static
    {
        $this->activiteId = $activiteId;
        return $this;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMethodePaiement(): string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(string $methodePaiement): static
    {
        $this->methodePaiement = $methodePaiement;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getReferenceExterne(): ?string
    {
        return $this->referenceExterne;
    }

    public function setReferenceExterne(?string $referenceExterne): static
    {
        $this->referenceExterne = $referenceExterne;
        return $this;
    }

    public function getReferenceTransaction(): ?string
    {
        return $this->referenceTransaction;
    }

    public function setReferenceTransaction(?string $referenceTransaction): static
    {
        $this->referenceTransaction = $referenceTransaction;
        return $this;
    }

    public function getDetailsJson(): ?string
    {
        return $this->detailsJson;
    }

    public function setDetailsJson(?string $detailsJson): static
    {
        $this->detailsJson = $detailsJson;
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
