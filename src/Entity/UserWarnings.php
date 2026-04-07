<?php

namespace App\Entity;

use App\Repository\UserWarningsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserWarningsRepository::class)]
#[ORM\Table(name: 'user_warnings')]
class UserWarnings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $userId = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $warningCount = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?int $isBlocked = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $lastWarningAt = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $blockedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getWarningCount(): ?int
    {
        return $this->warningCount;
    }

    public function setWarningCount(?int $warningCount): static
    {
        $this->warningCount = $warningCount;
        return $this;
    }

    public function getIsBlocked(): ?int
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(?int $isBlocked): static
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function getLastWarningAt(): ?\DateTimeInterface
    {
        return $this->lastWarningAt;
    }

    public function setLastWarningAt(?\DateTimeInterface $lastWarningAt): static
    {
        $this->lastWarningAt = $lastWarningAt;
        return $this;
    }

    public function getBlockedAt(): ?\DateTimeInterface
    {
        return $this->blockedAt;
    }

    public function setBlockedAt(?\DateTimeInterface $blockedAt): static
    {
        $this->blockedAt = $blockedAt;
        return $this;
    }

}
