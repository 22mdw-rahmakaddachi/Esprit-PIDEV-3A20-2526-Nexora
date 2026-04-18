<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
#[ORM\Table(name: 'activite')]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le nom de l\'activité est obligatoire.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'Le type est obligatoire.')]
    #[Assert\Choice(choices: ['Sport','Culture','Gastronomie','Aventure','Bien-être','Autre'], message: 'Type invalide.')]
    private ?string $type = null;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('MASCULIN','FEMININ','MIXTE') DEFAULT 'MIXTE'")]
    #[Assert\NotBlank(message: 'Le genre cible est obligatoire.')]
    #[Assert\Choice(choices: ['MASCULIN','FEMININ','MIXTE'], message: 'Genre cible invalide.')]
    private ?string $genreCible = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    #[Assert\Length(max: 120, maxMessage: 'Le lieu ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $lieu = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateActivite = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Partenaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le partenaire est obligatoire.')]
    private ?Partenaire $partenaire = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $images = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'Le prix est obligatoire.')]
    #[Assert\Positive(message: 'Le prix doit être supérieur à 0.')]
    #[Assert\LessThanOrEqual(value: 99999.99, message: 'Le prix ne peut pas dépasser 99 999,99 TND.')]
    private float $prix = 0.0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: 'Le nombre de places est obligatoire.')]
    #[Assert\Positive(message: 'Le nombre de places doit être supérieur à 0.')]
    #[Assert\LessThanOrEqual(value: 10000, message: 'Le nombre de places ne peut pas dépasser 10 000.')]
    private int $nombrePlaces = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Les places disponibles ne peuvent pas être négatives.')]
    private int $placesDisponibles = 0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'boolean')]
    private bool $avecDate = false;

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): static { $this->type = $type; return $this; }

    public function getGenreCible(): ?string { return $this->genreCible; }
    public function setGenreCible(?string $genreCible): static { $this->genreCible = $genreCible; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getDateActivite(): ?\DateTimeInterface { return $this->dateActivite; }
    public function setDateActivite(?\DateTimeInterface $dateActivite): static { $this->dateActivite = $dateActivite; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getPartenaire(): ?Partenaire { return $this->partenaire; }
    public function setPartenaire(?Partenaire $partenaire): static { $this->partenaire = $partenaire; return $this; }

    public function getImages(): ?string { return $this->images; }
    public function setImages(?string $images): static { $this->images = $images; return $this; }

    public function getImageUrl(): string
    {
        if (!$this->images) return '';
        if (str_starts_with($this->images, 'http')) return $this->images;
        return '/uploads/activites/' . $this->images;
    }

    public function getPrix(): float { return (float)$this->prix; }
    public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

    public function getNombrePlaces(): int { return $this->nombrePlaces; }
    public function setNombrePlaces(int $nombrePlaces): static { $this->nombrePlaces = $nombrePlaces; return $this; }

    public function getPlacesDisponibles(): int { return $this->placesDisponibles; }
    public function setPlacesDisponibles(int $placesDisponibles): static { $this->placesDisponibles = $placesDisponibles; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getAvecDate(): bool { return $this->avecDate; }
    public function setAvecDate(bool $avecDate): static { $this->avecDate = $avecDate; return $this; }
}
