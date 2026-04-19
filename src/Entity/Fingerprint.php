<?php

namespace App\Entity;

use App\Repository\FingerprintRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FingerprintRepository::class)]
#[ORM\Table(name: 'fingerprint')]
class Fingerprint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $fingerId = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getFingerId(): ?int { return $this->fingerId; }
    public function setFingerId(?int $fingerId): static { $this->fingerId = $fingerId; return $this; }

    public function getUser(): ?Users { return $this->user; }
    public function setUser(?Users $user): static { $this->user = $user; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getLastUsedAt(): ?\DateTimeImmutable { return $this->lastUsedAt; }
    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): static { $this->lastUsedAt = $lastUsedAt; return $this; }
}
