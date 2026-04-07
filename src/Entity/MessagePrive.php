<?php

namespace App\Entity;

use App\Repository\MessagePriveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagePriveRepository::class)]
#[ORM\Table(name: 'message_prive')]
class MessagePrive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $conversationId = 0;

    #[ORM\Column(type: 'integer')]
    private int $senderId = 0;

    #[ORM\Column(type: 'text')]
    private string $contenu = '';

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $sentAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversationId(): int
    {
        return $this->conversationId;
    }

    public function setConversationId(int $conversationId): static
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function setSenderId(int $senderId): static
    {
        $this->senderId = $senderId;
        return $this;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

}
