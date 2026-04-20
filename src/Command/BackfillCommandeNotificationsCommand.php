<?php

namespace App\Command;

use App\Entity\CommandeNotification;
use App\Repository\CommandeRepository;
use App\Repository\CommandeNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:backfill-commande-notifications', description: 'Crée les notifications manquantes pour les commandes existantes')]
class BackfillCommandeNotificationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommandeRepository $commandeRepo,
        private CommandeNotificationRepository $notifRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandes = $this->commandeRepo->findAll();
        $created = 0;

        foreach ($commandes as $commande) {
            // Déjà une notification pour cette commande ?
            $existing = $this->notifRepo->findOneBy(['commandeId' => $commande->getId()]);
            if ($existing) continue;

            // Récupérer les items via SQL brut
            $rows = $this->em->getConnection()->fetchAllAssociative(
                'SELECT ci.produit_nom, ci.quantite, ci.prix_unitaire, ci.sous_total,
                        pp.partenaire_id, pp.nom as pp_nom
                 FROM commande_item ci
                 LEFT JOIN produit_parent pp ON ci.produit_nom LIKE CONCAT(pp.nom, " %")
                 WHERE ci.commande_id = ?',
                [$commande->getId()]
            );

            // Regrouper par partenaire
            $parPartenaire = [];
            foreach ($rows as $row) {
                $pid = (int)($row['partenaire_id'] ?? 0);
                if (!$pid) {
                    // Fallback : partenaire 1 si non trouvé
                    $pid = 1;
                }
                $parPartenaire[$pid][] = $row;
            }

            foreach ($parPartenaire as $partenaireId => $items) {
                $details = implode("\n", array_map(fn($r) =>
                    sprintf('%s × %d — %.2f TND', $r['produit_nom'], $r['quantite'], $r['sous_total']),
                    $items
                ));
                $montant = array_sum(array_column($items, 'sous_total'));

                $notif = new CommandeNotification();
                $notif->setPartenaireId($partenaireId);
                $notif->setCommandeId($commande->getId());
                $notif->setClientNom($commande->getClientNom());
                $notif->setClientEmail($commande->getClientEmail());
                $notif->setDetails($details);
                $notif->setMontant((float)$montant);
                $notif->setLue(false);
                $notif->setCreatedAt($commande->getDateCommande() ?? new \DateTime());
                $this->em->persist($notif);
                $created++;
            }
        }

        $this->em->flush();
        $output->writeln("<info>✅ $created notification(s) créée(s).</info>");
        return Command::SUCCESS;
    }
}
