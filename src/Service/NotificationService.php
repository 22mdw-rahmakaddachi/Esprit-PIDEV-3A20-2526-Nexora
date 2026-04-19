<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class NotificationService
{
    public function __construct(private Connection $conn) {}

    public function push(
        string $type,
        string $message,
        string $actor,
        ?string $targetUser = null,
        ?int $refId = null,
        ?string $refType = null
    ): void {
        if ($targetUser && $actor === $targetUser) return;

        $titres = [
            'reaction'    => '👍 Nouvelle réaction',
            'commentaire' => '💬 Nouveau commentaire',
            'avis'        => '⭐ Nouvel avis',
        ];

        $this->conn->insert('notification', [
            'user_id'       => null,
            'user_type'     => 'CLIENT',
            'type'          => strtoupper($type),
            'titre'         => $titres[$type] ?? '🔔 Notification',
            'message'       => $message,
            'lue'           => 0,
            'date_creation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'activite_id'   => null,
            'demande_id'    => null,
        ]);
    }

    public function getSince(int $sinceId, int $limit = 20): array
    {
        $rows = $this->conn->fetchAllAssociative(
            'SELECT * FROM notification WHERE id > ? ORDER BY id DESC LIMIT ?',
            [$sinceId, $limit]
        );

        return array_map(fn($n) => [
            'id'         => (int) $n['id'],
            'type'       => strtolower($n['type']),
            'message'    => $n['message'],
            'titre'      => $n['titre'],
            'created_at' => $n['date_creation'],
            'is_read'    => (int) $n['lue'],
        ], $rows);
    }

    public function getAll(?string $targetUser = null, int $limit = 30): array
    {
        return $this->getSince(0, $limit);
    }

    public function markAllRead(?string $targetUser = null): void
    {
        $this->conn->executeStatement('UPDATE notification SET lue = 1 WHERE lue = 0');
    }

    public function countUnread(?string $targetUser = null): int
    {
        return (int) $this->conn->fetchOne('SELECT COUNT(*) FROM notification WHERE lue = 0');
    }
}
