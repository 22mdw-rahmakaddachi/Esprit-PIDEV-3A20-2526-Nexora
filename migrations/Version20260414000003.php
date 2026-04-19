<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414000003 extends AbstractMigration
{
    public function getDescription(): string { return 'Add image column to avis'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis DROP COLUMN image');
    }
}
