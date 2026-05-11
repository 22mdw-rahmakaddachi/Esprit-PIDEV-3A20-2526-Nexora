<?php

namespace App\Entity;

use App\Repository\ProduitParentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitParentRepository::class)]
#[ORM\Table(name: 'produit_parent')]
class ProduitParent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $partenaireId = 0;

    #[ORM\Column(type: 'integer')]
    private int $sousCategorieId = 0;

    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(min: 3, max: 200, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(length: 200)]
    private string $nom = '';

    #[Assert\NotBlank(message: 'La description complète est obligatoire.')]
    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Assert\NotBlank(message: 'La description courte est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'La description courte ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(nullable: true, length: 255)]
    private ?string $descriptionCourte = null;

    #[Assert\NotBlank(message: 'La marque est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'La marque ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(nullable: true, length: 100)]
    private ?string $marque = null;

    #[Assert\NotBlank(message: 'Le matériau est obligatoire.')]
    #[Assert\Length(max: 500, maxMessage: 'Le matériau ne peut pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $materiau = null;

    #[Assert\NotNull(message: 'Le poids est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le poids doit être un nombre positif ou zéro.')]
    #[ORM\Column(type: 'decimal', nullable: true, precision: 5, scale: 2)]
    private ?string $poidsKg = null;

    #[Assert\Length(max: 50, maxMessage: 'Les dimensions ne peuvent pas dépasser {{ limit }} caractères.')]
    #[ORM\Column(nullable: true, length: 50)]
    private ?string $dimensionsCm = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $imagePrincipale = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateAjout = null;

    #[Assert\Choice(choices: ['actif', 'inactif'], message: 'Le statut doit être "actif" ou "inactif".')]
    #[ORM\Column(nullable: true)]
    private ?string $statut = null;

    /** @var Collection<int, ProduitVariant> */
    #[ORM\OneToMany(targetEntity: ProduitVariant::class, mappedBy: 'produitParent', cascade: ['persist', 'remove'])]
    private Collection $variants;

    #[ORM\ManyToOne(targetEntity: SousCategorie::class)]
    #[ORM\JoinColumn(name: 'sous_categorie_id', referencedColumnName: 'id', nullable: true)]
    private ?SousCategorie $sousCategorie = null;

    #[ORM\ManyToOne(targetEntity: Partenaire::class)]
    #[ORM\JoinColumn(name: 'partenaire_id', referencedColumnName: 'id', nullable: true)]
    private ?Partenaire $partenaire = null;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->dateAjout = new \DateTime();
        $this->statut = 'actif';
    }

    public function getId(): ?int { return $this->id; }
    public function getPartenaireId(): int { return $this->partenaireId; }
    public function setPartenaireId(int $partenaireId): static { $this->partenaireId = $partenaireId; return $this; }
    public function getSousCategorieId(): int { return $this->sousCategorieId; }
    public function setSousCategorieId(int $sousCategorieId): static { $this->sousCategorieId = $sousCategorieId; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getDescriptionCourte(): ?string { return $this->descriptionCourte; }
    public function setDescriptionCourte(?string $descriptionCourte): static { $this->descriptionCourte = $descriptionCourte; return $this; }
    public function getMarque(): ?string { return $this->marque; }
    public function setMarque(?string $marque): static { $this->marque = $marque; return $this; }
    public function getMateriau(): ?string { return $this->materiau; }
    public function setMateriau(?string $materiau): static { $this->materiau = $materiau; return $this; }
    public function getPoidsKg(): ?float { return $this->poidsKg !== null ? (float)$this->poidsKg : null; }
    public function setPoidsKg(?float $poidsKg): static { $this->poidsKg = $poidsKg !== null ? (string)$poidsKg : null; return $this; }
    public function getDimensionsCm(): ?string { return $this->dimensionsCm; }
    public function setDimensionsCm(?string $dimensionsCm): static { $this->dimensionsCm = $dimensionsCm; return $this; }
    public function getImagePrincipale(): ?string { return $this->imagePrincipale; }
    public function setImagePrincipale(?string $imagePrincipale): static { $this->imagePrincipale = $imagePrincipale; return $this; }
    public function getDateAjout(): ?\DateTimeInterface { return $this->dateAjout; }
    public function setDateAjout(?\DateTimeInterface $dateAjout): static { $this->dateAjout = $dateAjout; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }
    /** @return Collection<int, ProduitVariant> */
    public function getVariants(): Collection { return $this->variants; }
    public function addVariant(ProduitVariant $variant): static { if (!$this->variants->contains($variant)) { $this->variants->add($variant); $variant->setProduitParent($this); } return $this; }
    public function removeVariant(ProduitVariant $variant): static { $this->variants->removeElement($variant); return $this; }
    public function getSousCategorie(): ?SousCategorie { return $this->sousCategorie; }
    public function setSousCategorie(?SousCategorie $sousCategorie): static { $this->sousCategorie = $sousCategorie; return $this; }
    public function getPartenaire(): ?Partenaire { return $this->partenaire; }
    public function setPartenaire(?Partenaire $partenaire): static { $this->partenaire = $partenaire; return $this; }
    public function getPrixMin(): ?float { if ($this->variants->isEmpty()) return null; $prix = []; foreach ($this->variants as $v) { $prix[] = $v->getPrixPromo() ?? $v->getPrixVente(); } return min($prix); }
}
