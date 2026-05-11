<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419163319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create commande_notification table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS commande_notification (
            id INT AUTO_INCREMENT NOT NULL,
            partenaire_id INT NOT NULL,
            commande_id INT NOT NULL,
            client_nom VARCHAR(100) NOT NULL,
            client_email VARCHAR(100) DEFAULT NULL,
            details LONGTEXT NOT NULL,
            montant NUMERIC(10, 2) NOT NULL,
            lue TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS commande_notification');
    }
}
