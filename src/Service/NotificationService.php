<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\ParticipationDemande;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(int $userId, string $userType, string $type, string $titre, string $message, ?int $activiteId = null, ?int $demandeId = null): void
    {
        // Vérifier que le user existe avant d'insérer
        $conn = $this->em->getConnection();
        $exists = $conn->fetchOne('SELECT COUNT(*) FROM users WHERE id = ?', [$userId]);
        if (!$exists) return;

        $n = new Notification();
        $n->setUserId($userId)
          ->setUserType($userType)
          ->setType($type)
          ->setTitre($titre)
          ->setMessage($message)
          ->setDateCreation(new \DateTime())
          ->setActiviteId($activiteId)
          ->setDemandeId($demandeId);
        $this->em->persist($n);
        $this->em->flush();
    }

    public function notifyAcceptation(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();
        $this->create(
            $demande->getClientId(), 'CLIENT', 'ACCEPTATION',
            '✅ Demande acceptée !',
            'Votre demande pour "' . $activite->getNom() . '" a été acceptée. Vous pouvez maintenant procéder au paiement.',
            $activite->getId(), $demande->getId()
        );
    }

    public function notifyRefus(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();
        $this->create(
            $demande->getClientId(), 'CLIENT', 'REFUS',
            '❌ Demande refusée',
            'Votre demande pour "' . $activite->getNom() . '" a été refusée.',
            $activite->getId(), $demande->getId()
        );
    }

    public function notifyNouvelleDemandePartenaire(ParticipationDemande $demande, int $partenaireUserId): void
    {
        $activite = $demande->getActivite();
        $this->create(
            $partenaireUserId, 'PARTENAIRE', 'NOUVELLE_DEMANDE',
            '📩 Nouvelle demande de participation',
            $demande->getClientNom() . ' souhaite participer à "' . $activite->getNom() . '".',
            $activite->getId(), $demande->getId()
        );
    }
}
