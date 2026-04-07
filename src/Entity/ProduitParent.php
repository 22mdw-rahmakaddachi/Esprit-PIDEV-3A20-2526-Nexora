<?php

namespace App\Entity;

use App\Repository\ProduitParentRepository;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(length: 200)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $descriptionCourte = null;

    #[ORM\Column(nullable: true, length: 100)]
    private ?string $marque = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $materiau = null;

    #[ORM\Column(type: 'decimal', nullable: true, precision: 5, scale: 2)]
    private ?float $poidsKg = null;

    #[ORM\Column(nullable: true, length: 50)]
    private ?string $dimensionsCm = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $imagePrincipale = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(nullable: true)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartenaireId(): int
    {
        return $this->partenaireId;
    }

    public function setPartenaireId(int $partenaireId): static
    {
        $this->partenaireId = $partenaireId;
        return $this;
    }

    public function getSousCategorieId(): int
    {
        return $this->sousCategorieId;
    }

    public function setSousCategorieId(int $sousCategorieId): static
    {
        $this->sousCategorieId = $sousCategorieId;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function getDescriptionCourte(): ?string
    {
        return $this->descriptionCourte;
    }

    public function setDescriptionCourte(?string $descriptionCourte): static
    {
        $this->descriptionCourte = $descriptionCourte;
        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;
        return $this;
    }

    public function getMateriau(): ?string
    {
        return $this->materiau;
    }

    public function setMateriau(?string $materiau): static
    {
        $this->materiau = $materiau;
        return $this;
    }

    public function getPoidsKg(): ?float
    {
        return $this->poidsKg;
    }

    public function setPoidsKg(?float $poidsKg): static
    {
        $this->poidsKg = $poidsKg;
        return $this;
    }

    public function getDimensionsCm(): ?string
    {
        return $this->dimensionsCm;
    }

    public function setDimensionsCm(?string $dimensionsCm): static
    {
        $this->dimensionsCm = $dimensionsCm;
        return $this;
    }

    public function getImagePrincipale(): ?string
    {
        return $this->imagePrincipale;
    }

    public function setImagePrincipale(?string $imagePrincipale): static
    {
        $this->imagePrincipale = $imagePrincipale;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(?\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
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

}
