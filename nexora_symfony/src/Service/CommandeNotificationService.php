<?php

namespace App\Service;

use App\Entity\CommandeNotification;
use App\Repository\ProduitParentRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommandeNotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProduitParentRepository $produitRepo
    ) {}

    /**
     * Crée une notification par partenaire concerné par la commande.
     * Chaque partenaire ne reçoit que les articles de ses propres produits.
     */
    public function notifierPartenaires(
        array $panier,
        int $commandeId,
        string $clientNom,
        ?string $clientEmail,
        float $totalFinal
    ): void {
        // Regrouper les items par partenaire
        $parPartenaire = [];

        foreach ($panier as $item) {
            $produitId = $item['produitId'] ?? null;
            if (!$produitId) continue;

            $produit = $this->produitRepo->find($produitId);
            if (!$produit) continue;

            $pid = $produit->getPartenaireId();
            if (!$pid) continue;

            $parPartenaire[$pid][] = $item;
        }

        // Une notification par partenaire avec seulement ses articles
        foreach ($parPartenaire as $partenaireId => $items) {
            $details = $this->buildDetails($items);
            $montant = array_sum(array_map(fn($i) => $i['prix'] * $i['quantite'], $items));

            $notif = new CommandeNotification();
            $notif->setPartenaireId($partenaireId);
            $notif->setCommandeId($commandeId);
            $notif->setClientNom($clientNom);
            $notif->setClientEmail($clientEmail);
            $notif->setDetails($details);
            $notif->setMontant($montant);
            $notif->setLue(false);

            $this->em->persist($notif);
        }

        $this->em->flush();
    }

    private function buildDetails(array $items): string
    {
        $lines = [];
        foreach ($items as $item) {
            $label = $item['variantLabel'] ?? ($item['sku'] ?? '');
            $lines[] = sprintf(
                '%s (%s) × %d — %.2f TND',
                $item['nom'],
                $label,
                $item['quantite'],
                $item['prix'] * $item['quantite']
            );
        }
        return implode("\n", $lines);
    }
}
