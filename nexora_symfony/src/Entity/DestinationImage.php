<?php

namespace App\Entity;

use App\Repository\DestinationImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationImageRepository::class)]
#[ORM\Table(name: 'destination_image')]
class DestinationImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Destination::class, inversedBy: 'destinationImages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Destination $destination = null;

    #[ORM\Column(length: 255)]
    private ?string $chemin = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordre = 0;

    // ─── Getters / Setters ───────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getDestination(): ?Destination { return $this->destination; }
    public function setDestination(?Destination $destination): static
    {
        $this->destination = $destination;
        return $this;
    }

    public function getChemin(): ?string { return $this->chemin; }
    public function setChemin(string $chemin): static { $this->chemin = $chemin; return $this; }

    /** Retourne l'URL prête à afficher dans une balise <img> */
    public function getDisplayChemin(): string
    {
        return Destination::toDisplayUrl((string) $this->chemin);
    }

    public function getOrdre(): ?int { return $this->ordre; }
    public function setOrdre(?int $ordre): static { $this->ordre = $ordre; return $this; }
}
