<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Create missing publication tables and fix avis table.
 */
final class Version20260416000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sync database with new publications and avis features';
    }

    public function up(Schema $schema): void
    {
        // 1. Create Publication Table
        $this->addSql('CREATE TABLE IF NOT EXISTS publication (
            id INT AUTO_INCREMENT NOT NULL,
            auteur VARCHAR(100) NOT NULL,
            contenu LONGTEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 2. Create Publication Reaction Table
        $this->addSql('CREATE TABLE IF NOT EXISTS publication_reaction (
            id INT AUTO_INCREMENT NOT NULL,
            publication_id INT NOT NULL,
            auteur VARCHAR(100) NOT NULL,
            type_reaction VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_PUB_REAC (publication_id),
            CONSTRAINT FK_PUB_REAC FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 3. Create Publication Commentaire Table
        $this->addSql('CREATE TABLE IF NOT EXISTS publication_commentaire (
            id INT AUTO_INCREMENT NOT NULL,
            publication_id INT NOT NULL,
            auteur VARCHAR(100) NOT NULL,
            contenu LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_PUB_COMM (publication_id),
            CONSTRAINT FK_PUB_COMM FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 4. Fix Avis Table (Rename columns if they exist in old format, and add new ones)
        // We use check logic in SQL to handle cases where columns might already exist or have different names
        $this->addSql("SET @column_titre = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'titre' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_titre > 0, 'ALTER TABLE avis CHANGE titre auteur VARCHAR(100) NOT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        $this->addSql("SET @column_contenu = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'contenu' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_contenu > 0, 'ALTER TABLE avis CHANGE contenu commentaire LONGTEXT NOT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        $this->addSql("SET @column_note = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'note' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_note = 0, 'ALTER TABLE avis ADD note INT NOT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        $this->addSql("SET @column_actid = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'activite_id' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_actid = 0, 'ALTER TABLE avis ADD activite_id INT NOT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        $this->addSql("SET @column_actnom = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'activite_nom' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_actnom = 0, 'ALTER TABLE avis ADD activite_nom VARCHAR(100) DEFAULT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");
        
        $this->addSql("SET @column_userid = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'user_id' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_userid > 0, 'ALTER TABLE avis DROP user_id', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        $this->addSql("SET @column_rating = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'avis' AND column_name = 'rating' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@column_rating > 0, 'ALTER TABLE avis DROP rating', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS publication_commentaire');
        $this->addSql('DROP TABLE IF EXISTS publication_reaction');
        $this->addSql('DROP TABLE IF EXISTS publication');
    }
}
