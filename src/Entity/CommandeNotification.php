<?php

namespace App\Entity;

use App\Repository\CommandeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeNotificationRepository::class)]
#[ORM\Table(name: 'commande_notification')]
class CommandeNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'partenaire_id', type: 'integer')]
    private int $partenaireId = 0;

    #[ORM\Column(name: 'commande_id', type: 'integer')]
    private int $commandeId = 0;

    #[ORM\Column(name: 'client_nom', length: 100)]
    private string $clientNom = '';

    #[ORM\Column(name: 'client_email', nullable: true, length: 100)]
    private ?string $clientEmail = null;

    #[ORM\Column(type: 'text')]
    private string $details = '';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montant = '0.00';

    #[ORM\Column(type: 'boolean')]
    private bool $lue = false;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getPartenaireId(): int { return $this->partenaireId; }
    public function setPartenaireId(int $v): static { $this->partenaireId = $v; return $this; }

    public function getCommandeId(): int { return $this->commandeId; }
    public function setCommandeId(int $v): static { $this->commandeId = $v; return $this; }

    public function getClientNom(): string { return $this->clientNom; }
    public function setClientNom(string $v): static { $this->clientNom = $v; return $this; }

    public function getClientEmail(): ?string { return $this->clientEmail; }
    public function setClientEmail(?string $v): static { $this->clientEmail = $v; return $this; }

    public function getDetails(): string { return $this->details; }
    public function setDetails(string $v): static { $this->details = $v; return $this; }

    public function getMontant(): float { return (float)$this->montant; }
    public function setMontant(float $v): static { $this->montant = (string)$v; return $this; }

    public function isLue(): bool { return $this->lue; }
    public function setLue(bool $v): static { $this->lue = $v; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $v): static { $this->createdAt = $v; return $this; }
}
