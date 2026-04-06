<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $user1Id = 0;

    #[ORM\Column(type: 'integer')]
    private int $user2Id = 0;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1Id(): int
    {
        return $this->user1Id;
    }

    public function setUser1Id(int $user1Id): static
    {
        $this->user1Id = $user1Id;
        return $this;
    }

    public function getUser2Id(): int
    {
        return $this->user2Id;
    }

    public function setUser2Id(int $user2Id): static
    {
        $this->user2Id = $user2Id;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}
