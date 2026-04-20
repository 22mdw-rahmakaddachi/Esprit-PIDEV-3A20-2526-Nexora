<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Integration des tables du module excursion :
 * - destination_image, destination_participant, destination_message
 * - destination_avis, destination_avis_image
 * - publication, publication_reaction, publication_commentaire
 * - avis (version finale avec activite_id, auteur, commentaire, note)
 */
final class Version20260420000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Intégration module excursion : destination images/participants/messages/avis, publications, avis activités';
    }

    public function up(Schema $schema): void
    {
        // ── Nouvelles colonnes de la table destination ──
        $this->addSql("SET @col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'destination' AND column_name = 'capacite_max' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@col = 0, 'ALTER TABLE destination ADD capacite_max INT NOT NULL DEFAULT 5, ADD nb_participants INT NOT NULL DEFAULT 0, ADD date_lancement DATETIME DEFAULT NULL, ADD currency VARCHAR(50) DEFAULT NULL, ADD plug_type VARCHAR(50) DEFAULT NULL, ADD survival_phrases LONGTEXT DEFAULT NULL, ADD panorama_url LONGTEXT DEFAULT NULL, ADD reminder_sent TINYINT(1) NOT NULL DEFAULT 0, ADD programme LONGTEXT DEFAULT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");

        // ── destination_image ──
        $this->addSql('CREATE TABLE IF NOT EXISTS destination_image (
            id INT AUTO_INCREMENT NOT NULL,
            destination_id INT NOT NULL,
            chemin VARCHAR(255) NOT NULL,
            ordre INT DEFAULT 0,
            PRIMARY KEY(id),
            INDEX IDX_DEST_IMG (destination_id),
            CONSTRAINT FK_DEST_IMG FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── destination_participant ──
        $this->addSql('CREATE TABLE IF NOT EXISTS destination_participant (
            id INT AUTO_INCREMENT NOT NULL,
            destination_id INT NOT NULL,
            user_id INT NOT NULL,
            user_nom VARCHAR(100) NOT NULL,
            joined_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_DEST_PART (destination_id),
            CONSTRAINT FK_DEST_PART FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── destination_message ──
        $this->addSql('CREATE TABLE IF NOT EXISTS destination_message (
            id INT AUTO_INCREMENT NOT NULL,
            destination_id INT NOT NULL,
            user_id INT NOT NULL,
            user_nom VARCHAR(100) NOT NULL,
            contenu LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_DEST_MSG (destination_id),
            CONSTRAINT FK_DEST_MSG FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── destination_avis ──
        $this->addSql('CREATE TABLE IF NOT EXISTS destination_avis (
            id INT AUTO_INCREMENT NOT NULL,
            destination_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL,
            commentaire LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_DEST_AVIS_DEST (destination_id),
            INDEX IDX_DEST_AVIS_USER (user_id),
            CONSTRAINT FK_DEST_AVIS_DEST FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE,
            CONSTRAINT FK_DEST_AVIS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── destination_avis_image ──
        $this->addSql('CREATE TABLE IF NOT EXISTS destination_avis_image (
            id INT AUTO_INCREMENT NOT NULL,
            avis_id INT NOT NULL,
            chemin LONGTEXT NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_DEST_AVIS_IMG (avis_id),
            CONSTRAINT FK_DEST_AVIS_IMG FOREIGN KEY (avis_id) REFERENCES destination_avis (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── publication ──
        $this->addSql('CREATE TABLE IF NOT EXISTS publication (
            id INT AUTO_INCREMENT NOT NULL,
            auteur VARCHAR(100) NOT NULL,
            contenu LONGTEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ── publication_reaction ──
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

        // ── publication_commentaire ──
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

        // ── avis (version finale) ──
        // Vérifie si la table avis existe déjà avec l'ancienne structure et la met à jour
        $this->addSql("SET @avis_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'avis' AND table_schema = DATABASE())");
        $this->addSql("SET @sql = IF(@avis_exists = 0,
            'CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, activite_id INT NOT NULL, activite_nom VARCHAR(100) DEFAULT NULL, auteur VARCHAR(100) NOT NULL, commentaire LONGTEXT NOT NULL, note INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB',
            'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS destination_avis_image');
        $this->addSql('DROP TABLE IF EXISTS destination_avis');
        $this->addSql('DROP TABLE IF EXISTS destination_message');
        $this->addSql('DROP TABLE IF EXISTS destination_participant');
        $this->addSql('DROP TABLE IF EXISTS destination_image');
        $this->addSql('DROP TABLE IF EXISTS publication_commentaire');
        $this->addSql('DROP TABLE IF EXISTS publication_reaction');
        $this->addSql('DROP TABLE IF EXISTS publication');
    }
}
