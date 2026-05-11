<?php

namespace App\Service;

use App\Entity\ParticipationDemande;
use Doctrine\DBAL\Connection;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Service de notifications pour les publications.
 * Utilise le bundle externe symfony/mailer pour envoyer des emails
 * + stockage en base (table pub_notification) pour l'affichage en temps réel.
 */
class NotificationService
{
    public function __construct(
        private Connection      $conn,
        private MailerInterface $mailer   // ← Bundle symfony/mailer
    ) {}

    /**
     * Créer une notification en base + envoyer un email via symfony/mailer
     */
    public function create(
        int     $userId,
        string  $type,
        string  $message,
        ?int    $relatedId   = null,
        ?string $relatedType = null
    ): void {
        $titre = match($type) {
            'comment'    => '💬 Nouveau commentaire',
            'reaction'   => '❤️ Nouvelle réaction',
            'demande'    => '📋 Nouvelle demande de participation',
            'acceptation'=> '✅ Demande acceptée',
            'refus'      => '❌ Demande refusée',
            default      => '🔔 Notification',
        };

        // ── 1. Stocker en base pour la cloche temps réel ──
        $this->conn->insert('pub_notification', [
            'user_id'    => $userId,
            'type'       => $type,
            'titre'      => $titre,
            'message'    => $message,
            'related_id' => $relatedId,
            'is_read'    => 0,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        // ── 2. Envoyer un email via symfony/mailer (bundle externe) ──
        $userEmail = $this->conn->fetchOne(
            'SELECT email FROM users WHERE id = ?',
            [$userId]
        );

        if ($userEmail) {
            $this->envoyerEmail($userEmail, $titre, $message, $relatedId);
        }
    }

    /**
     * Envoie un email de notification via le bundle symfony/mailer
     */
    private function envoyerEmail(
        string $to,
        string $titre,
        string $message,
        ?int   $relatedId
    ): void {
        $lien = $relatedId
            ? "http://localhost:8000/publications#pub-{$relatedId}"
            : "http://localhost:8000/publications";

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;
                    padding:24px;border:1px solid #ede5ff;border-radius:12px'>
            <div style='text-align:center;margin-bottom:20px'>
                <h2 style='color:#6c3fc5;margin:0'>{$titre}</h2>
                <p style='color:#888;margin:8px 0 0'>Nexora — Notifications</p>
            </div>
            <div style='background:#f9f7ff;border-radius:10px;padding:16px;
                        margin-bottom:20px;border-left:4px solid #6c3fc5'>
                <p style='margin:0;color:#333;font-size:15px'>{$message}</p>
            </div>
            <div style='text-align:center'>
                <a href='{$lien}'
                   style='background:linear-gradient(135deg,#6c3fc5,#9b59b6);
                          color:#fff;padding:12px 28px;border-radius:50px;
                          text-decoration:none;font-weight:600;font-size:14px'>
                    Voir la publication →
                </a>
            </div>
            <p style='color:#aaa;font-size:11px;text-align:center;margin-top:20px'>
                Nexora — Notification automatique
            </p>
        </div>";

        try {
            $email = (new Email())
                ->from($_ENV['MAILER_FROM'] ?? 'noreply@nexora.tn')
                ->to($to)
                ->subject($titre)
                ->html($html);

            $this->mailer->send($email);
        } catch (\Throwable) {
            // Ne pas bloquer si l'email échoue
        }
    }

    public function getUnread(int $userId): array
    {
        return $this->conn->fetchAllAssociative(
            'SELECT * FROM pub_notification WHERE user_id = ? AND is_read = 0 ORDER BY id DESC',
            [$userId]
        );
    }

    public function countUnread(int $userId): int
    {
        return (int) $this->conn->fetchOne(
            'SELECT COUNT(*) FROM pub_notification WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    public function markAsRead(int $id): void
    {
        $this->conn->update('pub_notification', ['is_read' => 1], ['id' => $id]);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->conn->executeStatement(
            'UPDATE pub_notification SET is_read = 1 WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    public function getAll(int $userId, int $limit = 20): array
    {
        return $this->conn->fetchAllAssociative(
            'SELECT * FROM pub_notification WHERE user_id = ? ORDER BY id DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    /**
     * Notifie le partenaire qu'une nouvelle demande de participation a été soumise.
     */
    public function notifyNouvelleDemandePartenaire(ParticipationDemande $demande, int $partenaireUserId): void
    {
        $activiteNom = $demande->getActivite()?->getNom() ?? 'une activité';
        $clientNom   = $demande->getClientNom();

        $message = "📋 {$clientNom} a soumis une demande de participation pour « {$activiteNom} ».";

        $this->create(
            $partenaireUserId,
            'demande',
            $message,
            $demande->getId(),
            'participation_demande'
        );
    }

    /**
     * Notifie le client que sa demande a été acceptée.
     */
    public function notifyAcceptation(ParticipationDemande $demande): void
    {
        $activiteNom = $demande->getActivite()?->getNom() ?? 'une activité';
        $message     = "✅ Votre demande de participation pour « {$activiteNom} » a été acceptée !";

        $this->create(
            $demande->getClientId(),
            'acceptation',
            $message,
            $demande->getId(),
            'participation_demande'
        );
    }

    /**
     * Notifie le client que sa demande a été refusée.
     */
    public function notifyRefus(ParticipationDemande $demande): void
    {
        $activiteNom = $demande->getActivite()?->getNom() ?? 'une activité';
        $message     = "❌ Votre demande de participation pour « {$activiteNom} » a été refusée.";

        $this->create(
            $demande->getClientId(),
            'refus',
            $message,
            $demande->getId(),
            'participation_demande'
        );
    }
}
