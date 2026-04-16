<?php

namespace App\Entity;

use App\Repository\DestinationAvisImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationAvisImageRepository::class)]
#[ORM\Table(name: 'destination_avis_image')]
class DestinationAvisImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $chemin = null;

    #[ORM\ManyToOne(targetEntity: DestinationAvis::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DestinationAvis $avis = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(string $chemin): self
    {
        $this->chemin = $chemin;
        return $this;
    }

    public function getAvis(): ?DestinationAvis
    {
        return $this->avis;
    }

    public function setAvis(?DestinationAvis $avis): self
    {
        $this->avis = $avis;
        return $this;
    }
}
