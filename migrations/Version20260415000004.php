<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415000004 extends AbstractMigration
{
    public function getDescription(): string { return 'Create notification table'; }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE notification (
            id INT AUTO_INCREMENT NOT NULL,
            type VARCHAR(50) NOT NULL COMMENT 'reaction|commentaire|avis',
            message VARCHAR(255) NOT NULL,
            target_user VARCHAR(100) DEFAULT NULL COMMENT 'auteur de la publication/avis ciblé',
            actor VARCHAR(100) NOT NULL COMMENT 'qui a fait l action',
            ref_id INT DEFAULT NULL COMMENT 'id publication ou avis',
            ref_type VARCHAR(30) DEFAULT NULL COMMENT 'publication|avis',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification');
    }
}
