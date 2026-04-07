<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406231055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, type VARCHAR(80) NOT NULL, genre_cible VARCHAR(255) NOT NULL, lieu VARCHAR(120) NOT NULL, date_activite DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, created_at TIME DEFAULT NULL, images LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, nombre_places INT NOT NULL, places_disponibles INT NOT NULL, date_creation DATE DEFAULT NULL, avec_date TINYINT(1) NOT NULL, INDEX IDX_B875551598DE13AC (partenaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribut_variation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, type_affichage LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rating INT NOT NULL, titre VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, activite_id INT NOT NULL, user_id INT NOT NULL, statut VARCHAR(255) NOT NULL, message VARCHAR(255) DEFAULT NULL, created_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, ordre_affichage INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE code_promo (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, type_reduction VARCHAR(255) NOT NULL, valeur_reduction NUMERIC(10, 2) NOT NULL, montant_minimum NUMERIC(10, 2) DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, limite_utilisation INT DEFAULT NULL, nombre_utilisations INT DEFAULT NULL, actif TINYINT(1) DEFAULT NULL, partenaire_id INT DEFAULT NULL, categorie_id INT DEFAULT NULL, premiere_commande_seulement TINYINT(1) DEFAULT NULL, date_creation TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, client_nom VARCHAR(100) NOT NULL, client_email VARCHAR(100) DEFAULT NULL, date_commande DATE NOT NULL, total NUMERIC(10, 2) NOT NULL, statut VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande_item (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, produit_nom VARCHAR(100) NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, sous_total NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, avis_id INT NOT NULL, user_id INT NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, user1_id INT NOT NULL, user2_id INT NOT NULL, created_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE destination (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, localisation VARCHAR(150) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, images LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_prive (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, contenu LONGTEXT NOT NULL, sent_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, activite_id INT DEFAULT NULL, demande_id INT DEFAULT NULL, user_type VARCHAR(20) NOT NULL, type VARCHAR(50) NOT NULL, titre VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, lue TINYINT(1) DEFAULT NULL, date_creation TIME DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA9B0F88B1 (activite_id), INDEX IDX_BF5476CA80E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE option_variation (id INT AUTO_INCREMENT NOT NULL, attribut_id INT NOT NULL, valeur VARCHAR(100) NOT NULL, code_hexadecimal VARCHAR(7) DEFAULT NULL, ordre_affichage INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, demande_id INT DEFAULT NULL, client_id INT DEFAULT NULL, activite_id INT DEFAULT NULL, montant NUMERIC(10, 2) NOT NULL, methode_paiement VARCHAR(100) NOT NULL, statut VARCHAR(50) DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, transaction_id VARCHAR(255) DEFAULT NULL, reference_externe VARCHAR(255) DEFAULT NULL, reference_transaction VARCHAR(255) DEFAULT NULL, details_json LONGTEXT DEFAULT NULL, date_creation DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, produit_id INT DEFAULT NULL, variant_sku VARCHAR(100) DEFAULT NULL, produit_nom VARCHAR(200) DEFAULT NULL, prix_unitaire NUMERIC(10, 2) DEFAULT NULL, quantite INT NOT NULL, date_ajout TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partenaire (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom_entreprise VARCHAR(200) NOT NULL, ice VARCHAR(50) DEFAULT NULL, responsable_nom VARCHAR(100) DEFAULT NULL, responsable_telephone VARCHAR(20) DEFAULT NULL, adresse_entreprise LONGTEXT DEFAULT NULL, site_web VARCHAR(200) DEFAULT NULL, description LONGTEXT DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, date_validation DATE DEFAULT NULL, commission NUMERIC(5, 2) DEFAULT NULL, date_inscription TIME DEFAULT NULL, INDEX IDX_32FFA373A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participation_demande (id INT AUTO_INCREMENT NOT NULL, activite_id INT DEFAULT NULL, client_id INT DEFAULT NULL, client_nom VARCHAR(255) NOT NULL, client_email VARCHAR(255) NOT NULL, client_telephone VARCHAR(20) NOT NULL, statut VARCHAR(50) DEFAULT NULL, date_demande DATETIME NOT NULL, paiement_effectue TINYINT(1) DEFAULT NULL, INDEX IDX_6DEA66649B0F88B1 (activite_id), INDEX IDX_6DEA666419EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_parent (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT NOT NULL, sous_categorie_id INT NOT NULL, nom VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, description_courte VARCHAR(255) DEFAULT NULL, marque VARCHAR(100) DEFAULT NULL, materiau LONGTEXT DEFAULT NULL, poids_kg NUMERIC(5, 2) DEFAULT NULL, dimensions_cm VARCHAR(50) DEFAULT NULL, image_principale VARCHAR(255) DEFAULT NULL, date_ajout TIME DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_variant (id INT AUTO_INCREMENT NOT NULL, produit_parent_id INT NOT NULL, sku VARCHAR(100) NOT NULL, prix_achat NUMERIC(10, 2) DEFAULT NULL, prix_vente NUMERIC(10, 2) NOT NULL, prix_promo NUMERIC(10, 2) DEFAULT NULL, quantite_stock INT NOT NULL, seuil_alerte INT DEFAULT NULL, image_specifique VARCHAR(255) DEFAULT NULL, code_barres VARCHAR(50) DEFAULT NULL, date_creation TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produits (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, quantite INT DEFAULT NULL, partenaire_id INT NOT NULL, categorie VARCHAR(100) DEFAULT NULL, image_url VARCHAR(500) DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, created_at TIME DEFAULT NULL, updated_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, activite_id INT DEFAULT NULL, description LONGTEXT NOT NULL, statut VARCHAR(50) DEFAULT NULL, date_creation TIME DEFAULT NULL, INDEX IDX_CE60640419EB6921 (client_id), INDEX IDX_CE6064049B0F88B1 (activite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sous_categorie (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_warnings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, warning_count INT DEFAULT NULL, is_blocked TINYINT(1) DEFAULT NULL, last_warning_at TIME DEFAULT NULL, blocked_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(50) NOT NULL, nom VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, num INT NOT NULL, role VARCHAR(50) NOT NULL, mdp VARCHAR(255) NOT NULL, tentative INT NOT NULL, validation TINYINT(1) NOT NULL, block_until BIGINT NOT NULL, block_level INT NOT NULL, reset_code VARCHAR(10) DEFAULT NULL, reset_expiration BIGINT DEFAULT NULL, finger_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisation_code_promo (id INT AUTO_INCREMENT NOT NULL, code_promo_id INT NOT NULL, client_id INT NOT NULL, commande_id INT NOT NULL, montant_reduction NUMERIC(10, 2) NOT NULL, date_utilisation TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE variant_option (id INT AUTO_INCREMENT NOT NULL, variant_id INT DEFAULT NULL, INDEX IDX_4FDCA7663B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B875551598DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA80E95E18 FOREIGN KEY (demande_id) REFERENCES participation_demande (id)');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT FK_32FFA373A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA66649B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA666419EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640419EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064049B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE variant_option ADD CONSTRAINT FK_4FDCA7663B69A9AF FOREIGN KEY (variant_id) REFERENCES produit_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B875551598DE13AC');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9B0F88B1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA80E95E18');
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY FK_32FFA373A76ED395');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA66649B0F88B1');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA666419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064049B0F88B1');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7663B69A9AF');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE attribut_variation');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE code_promo');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_item');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE destination');
        $this->addSql('DROP TABLE message_prive');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE option_variation');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE partenaire');
        $this->addSql('DROP TABLE participation_demande');
        $this->addSql('DROP TABLE produit_parent');
        $this->addSql('DROP TABLE produit_variant');
        $this->addSql('DROP TABLE produits');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE sous_categorie');
        $this->addSql('DROP TABLE user_warnings');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE utilisation_code_promo');
        $this->addSql('DROP TABLE variant_option');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
