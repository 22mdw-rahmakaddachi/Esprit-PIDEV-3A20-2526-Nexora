<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421122800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create publication_warning table for moderation system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS publication_warning (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_email VARCHAR(255) DEFAULT NULL,
            user_nom VARCHAR(255) DEFAULT NULL,
            contenu_bloque TEXT DEFAULT NULL,
            warning_count INT DEFAULT 1,
            is_blocked TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX IDX_PUB_WARN_USER (user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS publication_warning');
    }
}
