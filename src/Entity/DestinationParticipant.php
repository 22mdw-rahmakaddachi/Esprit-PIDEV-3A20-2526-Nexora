<?php

namespace App\Entity;

use App\Repository\DestinationParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationParticipantRepository::class)]
#[ORM\Table(name: 'destination_participant')]
class DestinationParticipant
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

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $joinedAt = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getDestination(): ?Destination { return $this->destination; }
    public function setDestination(?Destination $v): static { $this->destination = $v; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(int $v): static { $this->userId = $v; return $this; }

    public function getUserNom(): ?string { return $this->userNom; }
    public function setUserNom(string $v): static { $this->userNom = $v; return $this; }

    public function getJoinedAt(): ?\DateTimeInterface { return $this->joinedAt; }
    public function setJoinedAt(\DateTimeInterface $v): static { $this->joinedAt = $v; return $this; }
}
