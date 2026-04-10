<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
#[ORM\Table(name: 'destination')]
class Destination
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(options: ['default' => 5])]
    private int $capaciteMax = 5;

    #[ORM\Column(options: ['default' => 0])]
    private int $nbParticipants = 0;

    /**
     * Ancien champ texte conservé pour rétro-compatibilité
     * (les nouvelles images passent par la relation OneToMany)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $images = null;

    /**
     * @var Collection<int, DestinationImage>
     */
    #[ORM\OneToMany(
        mappedBy: 'destination',
        targetEntity: DestinationImage::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $destinationImages;

    public function __construct()
    {
        $this->destinationImages = new ArrayCollection();
    }

    // ─── Getters / Setters simples ───────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }

    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(?string $v): static { $this->localisation = $v; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $v): static { $this->statut = $v; return $this; }

    public function getCapaciteMax(): int { return $this->capaciteMax; }
    public function setCapaciteMax(int $v): static { $this->capaciteMax = $v; return $this; }

    public function getNbParticipants(): int { return $this->nbParticipants; }
    public function setNbParticipants(int $v): static { $this->nbParticipants = $v; return $this; }

    public function getImages(): ?string { return $this->images; }
    public function setImages(?string $v): static { $this->images = $v; return $this; }

    // ─── Relation OneToMany ──────────────────────────────────────────

    /** @return Collection<int, DestinationImage> */
    public function getDestinationImages(): Collection
    {
        return $this->destinationImages;
    }

    public function addDestinationImage(DestinationImage $image): static
    {
        if (!$this->destinationImages->contains($image)) {
            $this->destinationImages->add($image);
            $image->setDestination($this);
        }
        return $this;
    }

    public function removeDestinationImage(DestinationImage $image): static
    {
        if ($this->destinationImages->removeElement($image)) {
            if ($image->getDestination() === $this) {
                $image->setDestination(null);
            }
        }
        return $this;
    }




    /** Première image (URL prête à afficher) ou chaîne vide */
    public function getFirstImage(): string
    {
        if (!$this->destinationImages->isEmpty()) {
            return self::toDisplayUrl((string) $this->destinationImages->first()->getChemin());
        }
        if ($this->images) {
            $list = array_filter(array_map('trim', explode(',', $this->images)));
            return self::toDisplayUrl((string) array_shift($list));
        }
        return '';
    }

    /** Retourne tous les chemins en format affichable */
    public function getImagesList(): array
    {
        if (!$this->destinationImages->isEmpty()) {
            return array_map(
                fn(DestinationImage $img) => self::toDisplayUrl($img->getChemin()),
                $this->destinationImages->toArray()
            );
        }
        if ($this->images) {
            return array_values(array_filter(array_map(
                fn($p) => self::toDisplayUrl(trim($p)),
                explode(',', $this->images)
            )));
        }
        return [];
    }

    /**
     * Convertit n'importe quel format d'URL Google Drive vers le format thumbnail
     * (plus fiable pour les balises <img>).
     * Les chemins locaux sont retournés tels quels.
     */
    public static function toDisplayUrl(string $url): string
    {
        // Chemin local → tel quel
        if (!str_contains($url, 'drive.google.com')) {
            return $url;
        }
        // Déjà au bon format thumbnail → tel quel
        if (str_contains($url, 'thumbnail?id=')) {
            return $url;
        }
        // Extraire le fileId depuis uc?id=, ?id=, /d/
        $fileId = null;
        if (preg_match('/[?&]id=([a-zA-Z0-9_\-]+)/', $url, $m)) {
            $fileId = $m[1];
        } elseif (preg_match('/\/d\/([a-zA-Z0-9_\-]+)/', $url, $m)) {
            $fileId = $m[1];
        }
        if ($fileId) {
            return 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=w1200';
        }
        return $url;
    }


    /** Nombre d'images */
    public function getImagesCount(): int
    {
        return $this->destinationImages->count();
    }
}
