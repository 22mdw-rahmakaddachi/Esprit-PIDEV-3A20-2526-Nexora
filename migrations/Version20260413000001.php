<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create avis table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE avis (
            id INT AUTO_INCREMENT NOT NULL,
            activite_id INT NOT NULL,
            activite_nom VARCHAR(100) DEFAULT NULL,
            auteur VARCHAR(100) NOT NULL,
            commentaire LONGTEXT NOT NULL,
            note INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE avis');
    }
}
