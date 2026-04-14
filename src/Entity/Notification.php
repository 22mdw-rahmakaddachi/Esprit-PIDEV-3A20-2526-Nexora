<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: 'L\'identifiant utilisateur est obligatoire.')]
    #[Assert\Positive(message: 'L\'identifiant utilisateur doit être positif.')]
    private int $userId = 0;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le type d\'utilisateur est obligatoire.')]
    #[Assert\Choice(choices: ['CLIENT','PARTENAIRE','ADMIN'], message: 'Type utilisateur invalide.')]
    private string $userType = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type de notification est obligatoire.')]
    private string $type = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private string $titre = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    private string $message = '';

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $lue = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive(message: 'L\'identifiant activité doit être positif.')]
    private ?int $activiteId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive(message: 'L\'identifiant demande doit être positif.')]
    private ?int $demandeId = null;

    public function getId(): ?int { return $this->id; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): static { $this->userId = $userId; return $this; }

    public function getUserType(): string { return $this->userType; }
    public function setUserType(string $userType): static { $this->userType = $userType; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }

    public function getLue(): ?bool { return $this->lue; }
    public function setLue(?bool $lue): static { $this->lue = $lue; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getActiviteId(): ?int { return $this->activiteId; }
    public function setActiviteId(?int $activiteId): static { $this->activiteId = $activiteId; return $this; }

    public function getDemandeId(): ?int { return $this->demandeId; }
    public function setDemandeId(?int $demandeId): static { $this->demandeId = $demandeId; return $this; }
}
