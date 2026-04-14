<?php

namespace App\Entity;

use App\Repository\ParticipationDemandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationDemandeRepository::class)]
#[ORM\Table(name: 'participation_demande')]
class ParticipationDemande
{
    const STATUT_ATTENTE  = 'EN_ATTENTE';
    const STATUT_ACCEPTEE = 'ACCEPTEE';
    const STATUT_REFUSEE  = 'REFUSEE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activite::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'activité est obligatoire.')]
    private ?Activite $activite = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $clientId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du client est obligatoire.')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')]
    private string $clientNom = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private string $clientEmail = '';

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    #[Assert\Regex(pattern: '/^\+?[0-9]{8,15}$/', message: 'Le numéro de téléphone n\'est pas valide (8 à 15 chiffres).')]
    private string $clientTelephone = '';

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: ['EN_ATTENTE','ACCEPTEE','REFUSEE'], message: 'Statut invalide.')]
    private ?string $statut = self::STATUT_ATTENTE;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'La date de demande est obligatoire.')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $paiementEffectue = false;

    public function getId(): ?int { return $this->id; }

    public function getActivite(): ?Activite { return $this->activite; }
    public function setActivite(?Activite $activite): static { $this->activite = $activite; return $this; }

    public function getClientId(): ?int { return $this->clientId; }
    public function setClientId(?int $clientId): static { $this->clientId = $clientId; return $this; }

    public function getClientNom(): string { return $this->clientNom; }
    public function setClientNom(string $clientNom): static { $this->clientNom = $clientNom; return $this; }

    public function getClientEmail(): string { return $this->clientEmail; }
    public function setClientEmail(string $clientEmail): static { $this->clientEmail = $clientEmail; return $this; }

    public function getClientTelephone(): string { return $this->clientTelephone; }
    public function setClientTelephone(string $clientTelephone): static { $this->clientTelephone = $clientTelephone; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }

    public function getDateDemande(): ?\DateTimeInterface { return $this->dateDemande; }
    public function setDateDemande(?\DateTimeInterface $dateDemande): static { $this->dateDemande = $dateDemande; return $this; }

    public function getPaiementEffectue(): ?bool { return $this->paiementEffectue; }
    public function setPaiementEffectue(?bool $paiementEffectue): static { $this->paiementEffectue = $paiementEffectue; return $this; }
}
