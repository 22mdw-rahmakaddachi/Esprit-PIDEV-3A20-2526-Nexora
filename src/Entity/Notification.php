<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;
use App\Entity\Activite;
use App\Entity\ParticipationDemande;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Users $user = null;

    #[ORM\Column(length: 20)]
    private string $userType = '';

    #[ORM\Column(length: 50)]
    private string $type = '';

    #[ORM\Column(length: 255)]
    private string $titre = '';

    #[ORM\Column(type: 'text')]
    private string $message = '';

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?int $lue = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Activite::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activite $activite = null;

    #[ORM\ManyToOne(targetEntity: ParticipationDemande::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ParticipationDemande $demande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): static
    {
        $this->userType = $userType;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getLue(): ?int
    {
        return $this->lue;
    }

    public function setLue(?int $lue): static
    {
        $this->lue = $lue;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): static
    {
        $this->activite = $activite;
        return $this;
    }

    public function getDemande(): ?ParticipationDemande
    {
        return $this->demande;
    }

    public function setDemande(?ParticipationDemande $demande): static
    {
        $this->demande = $demande;
        return $this;
    }

}
