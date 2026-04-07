<?php

namespace App\Entity;

use App\Repository\ParticipationDemandeRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Activite;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: ParticipationDemandeRepository::class)]
#[ORM\Table(name: 'participation_demande')]
class ParticipationDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activite::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activite $activite = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Users $client = null;

    #[ORM\Column(length: 255)]
    private string $clientNom = '';

    #[ORM\Column(length: 255)]
    private string $clientEmail = '';

    #[ORM\Column(length: 20)]
    private string $clientTelephone = '';

    #[ORM\Column(nullable: true, length: 50)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?int $paiementEffectue = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getClient(): ?Users
    {
        return $this->client;
    }

    public function setClient(?Users $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getClientNom(): string
    {
        return $this->clientNom;
    }

    public function setClientNom(string $clientNom): static
    {
        $this->clientNom = $clientNom;
        return $this;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getClientTelephone(): string
    {
        return $this->clientTelephone;
    }

    public function setClientTelephone(string $clientTelephone): static
    {
        $this->clientTelephone = $clientTelephone;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(?\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }

    public function getPaiementEffectue(): ?int
    {
        return $this->paiementEffectue;
    }

    public function setPaiementEffectue(?int $paiementEffectue): static
    {
        $this->paiementEffectue = $paiementEffectue;
        return $this;
    }

}
