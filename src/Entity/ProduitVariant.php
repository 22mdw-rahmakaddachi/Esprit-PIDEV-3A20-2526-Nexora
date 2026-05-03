<?php

namespace App\Entity;

use App\Repository\ProduitVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitVariantRepository::class)]
#[ORM\Table(name: 'produit_variant')]
class ProduitVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $produitParentId = 0;

    #[ORM\ManyToOne(targetEntity: ProduitParent::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'produit_parent_id', referencedColumnName: 'id', nullable: true)]
    private ?ProduitParent $produitParent = null;

    #[Assert\NotBlank(message: 'Le SKU est obligatoire.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le SKU doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le SKU ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[A-Z0-9_\-]+$/i', message: 'Le SKU ne peut contenir que des lettres, chiffres, tirets et underscores.')]
    #[ORM\Column(length: 100)]
    private string $sku = '';

    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?string $prixAchat = null;

    #[Assert\NotBlank(message: 'Le prix de vente est obligatoire.')]
    #[Assert\Positive(message: 'Le prix de vente doit être supérieur à zéro.')]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $prixVente = '0.00';

    #[Assert\Positive(message: 'Le prix promo doit être supérieur à zéro.')]
    #[ORM\Column(type: 'decimal', nullable: true, precision: 10, scale: 2)]
    private ?string $prixPromo = null;

    #[Assert\NotNull(message: 'Le stock est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le stock doit être positif ou zéro.')]
    #[ORM\Column(type: 'integer')]
    private int $quantiteStock = 0;

    #[Assert\PositiveOrZero(message: 'Le seuil d\'alerte doit être positif ou zéro.')]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $seuilAlerte = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $imageSpecifique = null;

    #[ORM\Column(nullable: true, length: 50)]
    private ?string $codeBarres = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    /** Options de ce variant (ex: Taille=L, Couleur=Rouge) */
    #[ORM\OneToMany(targetEntity: VariantOption::class, mappedBy: 'variant', cascade: ['persist', 'remove'])]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getProduitParentId(): int { return $this->produitParentId; }
    public function setProduitParentId(int $produitParentId): static { $this->produitParentId = $produitParentId; return $this; }

    public function getProduitParent(): ?ProduitParent { return $this->produitParent; }
    public function setProduitParent(?ProduitParent $produitParent): static
    {
        $this->produitParent = $produitParent;
        if ($produitParent) $this->produitParentId = $produitParent->getId() ?? 0;
        return $this;
    }

    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): static { $this->sku = $sku; return $this; }

    public function getPrixAchat(): ?float { return $this->prixAchat !== null ? (float)$this->prixAchat : null; }
    public function setPrixAchat(?float $prixAchat): static { $this->prixAchat = $prixAchat !== null ? (string)$prixAchat : null; return $this; }

    public function getPrixVente(): float { return (float)$this->prixVente; }
    public function setPrixVente(float $prixVente): static { $this->prixVente = (string)$prixVente; return $this; }

    public function getPrixPromo(): ?float { return $this->prixPromo !== null ? (float)$this->prixPromo : null; }
    public function setPrixPromo(?float $prixPromo): static { $this->prixPromo = $prixPromo !== null ? (string)$prixPromo : null; return $this; }

    public function getQuantiteStock(): int { return $this->quantiteStock; }
    public function setQuantiteStock(int $quantiteStock): static { $this->quantiteStock = $quantiteStock; return $this; }

    public function getSeuilAlerte(): ?int { return $this->seuilAlerte; }
    public function setSeuilAlerte(?int $seuilAlerte): static { $this->seuilAlerte = $seuilAlerte; return $this; }

    public function getImageSpecifique(): ?string { return $this->imageSpecifique; }
    public function setImageSpecifique(?string $imageSpecifique): static { $this->imageSpecifique = $imageSpecifique; return $this; }

    public function getCodeBarres(): ?string { return $this->codeBarres; }
    public function setCodeBarres(?string $codeBarres): static { $this->codeBarres = $codeBarres; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getOptions(): Collection { return $this->options; }
    public function addOption(VariantOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setVariant($this);
        }
        return $this;
    }
    public function removeOption(VariantOption $option): static { $this->options->removeElement($option); return $this; }

    /** Prix effectif (promo si dispo, sinon vente) */
    public function getPrixEffectif(): float
    {
        return $this->prixPromo ?? $this->prixVente;
    }

    /** Label lisible des options ex: "L / Rouge" */
    public function getOptionsLabel(): string
    {
        $parts = [];
        foreach ($this->options as $opt) {
            if ($opt->getOptionVariation()) {
                $parts[] = $opt->getOptionVariation()->getValeur();
            }
        }
        return implode(' / ', $parts) ?: $this->sku;
    }
}
