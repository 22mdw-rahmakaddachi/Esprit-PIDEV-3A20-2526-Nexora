<?php

namespace App\Entity;

use App\Repository\CodePromoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CodePromoRepository::class)]
#[ORM\Table(name: 'code_promo')]
class CodePromo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le code promo est obligatoire.')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'Le code doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[A-Z0-9_-]+$/i', message: 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.')]
    #[ORM\Column(length: 50)]
    private string $code = '';

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $description = null;

    #[Assert\NotBlank(message: 'Le type de réduction est obligatoire.')]
    #[Assert\Choice(choices: ['pourcentage', 'montant_fixe'], message: 'Le type doit être "pourcentage" ou "montant_fixe".')]
    #[ORM\Column]
    private string $typeReduction = '';

    #[Assert\NotBlank(message: 'La valeur de réduction est obligatoire.')]
    #[Assert\Positive(message: 'La valeur de réduction doit être positive.')]
    #[Assert\LessThanOrEqual(value: 100, message: 'Un pourcentage ne peut pas dépasser 100%.', groups: ['pourcentage'])]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $valeurReduction = '0.00';

    #[Assert\PositiveOrZero(message: 'Le montant minimum doit être positif ou zéro.')]
    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?string $montantMinimum = null;

    #[Assert\NotNull(message: 'La date de début est obligatoire.')]
    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[Assert\NotNull(message: 'La date de fin est obligatoire.')]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'La date de fin doit être postérieure à la date de début.')]
    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateFin = null;

    #[Assert\Positive(message: 'La limite d\'utilisation doit être un nombre positif.')]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $limiteUtilisation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombreUtilisations = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $partenaireId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $categorieId = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $premiereCommandeSeulement = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeReduction(): string
    {
        return $this->typeReduction;
    }

    public function setTypeReduction(string $typeReduction): static
    {
        $this->typeReduction = $typeReduction;
        return $this;
    }

    public function getValeurReduction(): float
    {
        return (float)$this->valeurReduction;
    }

    public function setValeurReduction(float $valeurReduction): static
    {
        $this->valeurReduction = (string)$valeurReduction;
        return $this;
    }

    public function getMontantMinimum(): ?float
    {
        return $this->montantMinimum !== null ? (float)$this->montantMinimum : null;
    }

    public function setMontantMinimum(?float $montantMinimum): static
    {
        $this->montantMinimum = $montantMinimum !== null ? (string)$montantMinimum : null;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getLimiteUtilisation(): ?int
    {
        return $this->limiteUtilisation;
    }

    public function setLimiteUtilisation(?int $limiteUtilisation): static
    {
        $this->limiteUtilisation = $limiteUtilisation;
        return $this;
    }

    public function getNombreUtilisations(): ?int
    {
        return $this->nombreUtilisations;
    }

    public function setNombreUtilisations(?int $nombreUtilisations): static
    {
        $this->nombreUtilisations = $nombreUtilisations;
        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getPartenaireId(): ?int
    {
        return $this->partenaireId;
    }

    public function setPartenaireId(?int $partenaireId): static
    {
        $this->partenaireId = $partenaireId;
        return $this;
    }

    public function getCategorieId(): ?int
    {
        return $this->categorieId;
    }

    public function setCategorieId(?int $categorieId): static
    {
        $this->categorieId = $categorieId;
        return $this;
    }

    public function getPremiereCommandeSeulement(): ?bool
    {
        return $this->premiereCommandeSeulement;
    }

    public function setPremiereCommandeSeulement(?bool $premiereCommandeSeulement): static
    {
        $this->premiereCommandeSeulement = $premiereCommandeSeulement;
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

}
