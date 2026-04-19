<?php

namespace App\Entity;

use App\Repository\VolRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolRepository::class)]
#[ORM\Table(name: 'vol')]
class Vol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $numero = '';

    #[ORM\Column(length: 10)]
    private string $depart = '';

    #[ORM\Column(length: 10)]
    private string $arrivee = '';

    #[ORM\Column(length: 50)]
    private string $statut = '';

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $heureDepart = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $heureArrivee = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getNumero(): string { return $this->numero; }
    public function setNumero(string $numero): static { $this->numero = $numero; return $this; }

    public function getDepart(): string { return $this->depart; }
    public function setDepart(string $depart): static { $this->depart = $depart; return $this; }

    public function getArrivee(): string { return $this->arrivee; }
    public function setArrivee(string $arrivee): static { $this->arrivee = $arrivee; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getHeureDepart(): ?string { return $this->heureDepart; }
    public function setHeureDepart(?string $heureDepart): static { $this->heureDepart = $heureDepart; return $this; }

    public function getHeureArrivee(): ?string { return $this->heureArrivee; }
    public function setHeureArrivee(?string $heureArrivee): static { $this->heureArrivee = $heureArrivee; return $this; }

    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
