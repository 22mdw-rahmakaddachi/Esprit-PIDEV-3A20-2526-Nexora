<?php

namespace App\Entity;

use App\Repository\DestinationMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationMessageRepository::class)]
#[ORM\Table(name: 'destination_message')]
class DestinationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Destination::class)]
    #[ORM\JoinColumn(name: 'destination_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Destination $destination = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column(length: 100)]
    private ?string $userNom = null;

    #[ORM\Column(type: 'text')]
    private ?string $contenu = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getDestination(): ?Destination { return $this->destination; }
    public function setDestination(?Destination $v): static { $this->destination = $v; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(int $v): static { $this->userId = $v; return $this; }

    public function getUserNom(): ?string { return $this->userNom; }
    public function setUserNom(string $v): static { $this->userNom = $v; return $this; }

    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $v): static { $this->contenu = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $v): static { $this->createdAt = $v; return $this; }
}
