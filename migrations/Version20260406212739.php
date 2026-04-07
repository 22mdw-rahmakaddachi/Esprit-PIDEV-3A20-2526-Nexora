<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406212739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rating INT NOT NULL, titre VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, avis_id INT NOT NULL, user_id INT NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE destination (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, localisation VARCHAR(150) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, images LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_warnings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, warning_count INT DEFAULT NULL, is_blocked TINYINT(1) DEFAULT NULL, last_warning_at TIME DEFAULT NULL, blocked_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY fk_activite_partenaire');
        $this->addSql('ALTER TABLE activite CHANGE partenaire_id partenaire_id INT DEFAULT NULL, CHANGE genre_cible genre_cible VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE created_at created_at TIME DEFAULT NULL, CHANGE images images LONGTEXT DEFAULT NULL, CHANGE prix prix NUMERIC(10, 2) NOT NULL, CHANGE nombre_places nombre_places INT NOT NULL, CHANGE places_disponibles places_disponibles INT NOT NULL, CHANGE avec_date avec_date TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B875551598DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('ALTER TABLE activite RENAME INDEX fk_activite_partenaire TO IDX_B875551598DE13AC');
        $this->addSql('DROP INDEX unique_attribut ON attribut_variation');
        $this->addSql('ALTER TABLE attribut_variation CHANGE type_affichage type_affichage LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_cand ON candidature');
        $this->addSql('DROP INDEX fk_candidature_user ON candidature');
        $this->addSql('ALTER TABLE candidature CHANGE statut statut VARCHAR(255) NOT NULL, CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE categorie CHANGE description description LONGTEXT DEFAULT NULL, CHANGE ordre_affichage ordre_affichage INT DEFAULT NULL');
        $this->addSql('DROP INDEX code ON code_promo');
        $this->addSql('DROP INDEX idx_code ON code_promo');
        $this->addSql('DROP INDEX idx_partenaire ON code_promo');
        $this->addSql('DROP INDEX idx_actif ON code_promo');
        $this->addSql('DROP INDEX idx_dates ON code_promo');
        $this->addSql('ALTER TABLE code_promo CHANGE montant_minimum montant_minimum NUMERIC(10, 2) DEFAULT NULL, CHANGE nombre_utilisations nombre_utilisations INT DEFAULT NULL, CHANGE actif actif TINYINT(1) DEFAULT NULL, CHANGE premiere_commande_seulement premiere_commande_seulement TINYINT(1) DEFAULT NULL, CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande CHANGE statut statut VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX commande_id ON commande_item');
        $this->addSql('DROP INDEX uniq_pair ON conversation');
        $this->addSql('DROP INDEX fk_conv_user2 ON conversation');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('DROP INDEX idx_conversation ON message_prive');
        $this->addSql('DROP INDEX idx_sender ON message_prive');
        $this->addSql('ALTER TABLE message_prive CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE sent_at sent_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY fk_notif_activite');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY fk_notif_demande');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY fk_notif_user');
        $this->addSql('DROP INDEX idx_user ON notification');
        $this->addSql('DROP INDEX idx_lue ON notification');
        $this->addSql('DROP INDEX idx_date ON notification');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT DEFAULT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE lue lue TINYINT(1) DEFAULT NULL, CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA80E95E18 FOREIGN KEY (demande_id) REFERENCES participation_demande (id)');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_activite TO IDX_BF5476CA9B0F88B1');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_demande TO IDX_BF5476CA80E95E18');
        $this->addSql('DROP INDEX unique_option ON option_variation');
        $this->addSql('ALTER TABLE option_variation CHANGE ordre_affichage ordre_affichage INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_B300236A51383AF3 ON option_variation (attribut_id)');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY fk_paiement_activite');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY fk_paiement_client');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY fk_paiement_demande');
        $this->addSql('DROP INDEX idx_paiement_commande ON paiement');
        $this->addSql('DROP INDEX idx_paiement_demande ON paiement');
        $this->addSql('DROP INDEX idx_paiement_client ON paiement');
        $this->addSql('DROP INDEX idx_paiement_activite ON paiement');
        $this->addSql('DROP INDEX idx_paiement_statut ON paiement');
        $this->addSql('ALTER TABLE paiement CHANGE statut statut VARCHAR(50) DEFAULT NULL, CHANGE details_json details_json LONGTEXT DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX unique_client_produit ON panier');
        $this->addSql('DROP INDEX produit_id ON panier');
        $this->addSql('DROP INDEX idx_variant_sku ON panier');
        $this->addSql('ALTER TABLE panier CHANGE quantite quantite INT NOT NULL, CHANGE date_ajout date_ajout TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire DROP INDEX user_id, ADD INDEX IDX_32FFA373A76ED395 (user_id)');
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY fk_partenaire_user');
        $this->addSql('ALTER TABLE partenaire CHANGE user_id user_id INT DEFAULT NULL, CHANGE adresse_entreprise adresse_entreprise LONGTEXT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE statut statut VARCHAR(50) DEFAULT NULL, CHANGE commission commission NUMERIC(5, 2) DEFAULT NULL, CHANGE date_inscription date_inscription TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT FK_32FFA373A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY fk_demande_activite');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY fk_demande_client');
        $this->addSql('ALTER TABLE participation_demande CHANGE activite_id activite_id INT DEFAULT NULL, CHANGE client_id client_id INT DEFAULT NULL, CHANGE statut statut VARCHAR(50) DEFAULT NULL, CHANGE paiement_effectue paiement_effectue TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA66649B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA666419EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE participation_demande RENAME INDEX idx_demande_activite_id TO IDX_6DEA66649B0F88B1');
        $this->addSql('ALTER TABLE participation_demande RENAME INDEX idx_demande_client_id TO IDX_6DEA666419EB6921');
        $this->addSql('ALTER TABLE produit_parent CHANGE description description LONGTEXT DEFAULT NULL, CHANGE materiau materiau LONGTEXT DEFAULT NULL, CHANGE date_ajout date_ajout DATETIME DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit_parent RENAME INDEX sous_categorie_id TO IDX_CADCAB75365BF48');
        $this->addSql('ALTER TABLE produit_parent RENAME INDEX partenaire_id TO IDX_CADCAB7598DE13AC');
        $this->addSql('DROP INDEX sku ON produit_variant');
        $this->addSql('ALTER TABLE produit_variant CHANGE quantite_stock quantite_stock INT NOT NULL, CHANGE seuil_alerte seuil_alerte INT DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE produit_variant RENAME INDEX produit_parent_id TO IDX_37B834D4F6ED949C');
        $this->addSql('DROP INDEX partenaire_id ON produits');
        $this->addSql('ALTER TABLE produits CHANGE description description LONGTEXT DEFAULT NULL, CHANGE quantite quantite INT DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at TIME DEFAULT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY reclamation_ibfk_1');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY reclamation_ibfk_2');
        $this->addSql('ALTER TABLE reclamation CHANGE client_id client_id INT DEFAULT NULL, CHANGE activite_id activite_id INT DEFAULT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE statut statut VARCHAR(50) DEFAULT NULL, CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640419EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064049B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE reclamation RENAME INDEX client_id TO IDX_CE60640419EB6921');
        $this->addSql('ALTER TABLE reclamation RENAME INDEX activite_id TO IDX_CE6064049B0F88B1');
        $this->addSql('DROP INDEX categorie_id ON sous_categorie');
        $this->addSql('ALTER TABLE sous_categorie CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX finger_id ON users');
        $this->addSql('ALTER TABLE users CHANGE tentative tentative INT NOT NULL, CHANGE validation validation TINYINT(1) NOT NULL, CHANGE block_until block_until BIGINT NOT NULL, CHANGE block_level block_level INT NOT NULL');
        $this->addSql('ALTER TABLE utilisation_code_promo DROP FOREIGN KEY utilisation_code_promo_ibfk_1');
        $this->addSql('DROP INDEX idx_client ON utilisation_code_promo');
        $this->addSql('DROP INDEX idx_code_promo ON utilisation_code_promo');
        $this->addSql('DROP INDEX idx_commande ON utilisation_code_promo');
        $this->addSql('ALTER TABLE utilisation_code_promo CHANGE date_utilisation date_utilisation DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX option_id ON variant_option');
        $this->addSql('ALTER TABLE variant_option ADD id INT AUTO_INCREMENT NOT NULL, ADD attribut_id INT DEFAULT NULL, ADD option_variation_id INT DEFAULT NULL, DROP option_id, CHANGE variant_id variant_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE INDEX IDX_4FDCA7663B69A9AF ON variant_option (variant_id)');
        $this->addSql('CREATE INDEX IDX_4FDCA76651383AF3 ON variant_option (attribut_id)');
        $this->addSql('CREATE INDEX IDX_4FDCA7669DA8FC14 ON variant_option (option_variation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, transporteur VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, numero_suivi VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, date_expedition DATE DEFAULT NULL, date_livraison_prevue DATE DEFAULT NULL, date_livraison_effective DATE DEFAULT NULL, statut VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'PREPARATION\' COLLATE `utf8mb4_unicode_ci`, INDEX commande_id (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE destination');
        $this->addSql('DROP TABLE user_warnings');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B875551598DE13AC');
        $this->addSql('ALTER TABLE activite CHANGE partenaire_id partenaire_id INT NOT NULL, CHANGE genre_cible genre_cible VARCHAR(255) DEFAULT \'MIXTE\' NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE images images TEXT DEFAULT NULL, CHANGE prix prix NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE nombre_places nombre_places INT DEFAULT 0 NOT NULL, CHANGE places_disponibles places_disponibles INT DEFAULT 0 NOT NULL, CHANGE avec_date avec_date TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT fk_activite_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaire (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite RENAME INDEX idx_b875551598de13ac TO fk_activite_partenaire');
        $this->addSql('ALTER TABLE attribut_variation CHANGE type_affichage type_affichage VARCHAR(255) DEFAULT \'DROPDOWN\'');
        $this->addSql('CREATE UNIQUE INDEX unique_attribut ON attribut_variation (nom)');
        $this->addSql('ALTER TABLE candidature CHANGE statut statut VARCHAR(255) DEFAULT \'EN_ATTENTE\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX uniq_cand ON candidature (activite_id, user_id)');
        $this->addSql('CREATE INDEX fk_candidature_user ON candidature (user_id)');
        $this->addSql('ALTER TABLE categorie CHANGE description description TEXT DEFAULT NULL, CHANGE ordre_affichage ordre_affichage INT DEFAULT 0');
        $this->addSql('ALTER TABLE code_promo CHANGE montant_minimum montant_minimum NUMERIC(10, 2) DEFAULT \'0.00\', CHANGE nombre_utilisations nombre_utilisations INT DEFAULT 0, CHANGE actif actif TINYINT(1) DEFAULT 1, CHANGE premiere_commande_seulement premiere_commande_seulement TINYINT(1) DEFAULT 0, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX code ON code_promo (code)');
        $this->addSql('CREATE INDEX idx_code ON code_promo (code)');
        $this->addSql('CREATE INDEX idx_partenaire ON code_promo (partenaire_id)');
        $this->addSql('CREATE INDEX idx_actif ON code_promo (actif)');
        $this->addSql('CREATE INDEX idx_dates ON code_promo (date_debut, date_fin)');
        $this->addSql('ALTER TABLE commande CHANGE statut statut VARCHAR(50) DEFAULT \'EN_ATTENTE\'');
        $this->addSql('CREATE INDEX commande_id ON commande_item (commande_id)');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX uniq_pair ON conversation (user1_id, user2_id)');
        $this->addSql('CREATE INDEX fk_conv_user2 ON conversation (user2_id)');
        $this->addSql('ALTER TABLE message_prive CHANGE contenu contenu TEXT NOT NULL, CHANGE sent_at sent_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX idx_conversation ON message_prive (conversation_id)');
        $this->addSql('CREATE INDEX idx_sender ON message_prive (sender_id)');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9B0F88B1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA80E95E18');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT NOT NULL, CHANGE message message TEXT NOT NULL, CHANGE lue lue TINYINT(1) DEFAULT 0, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_notif_activite FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_notif_demande FOREIGN KEY (demande_id) REFERENCES participation_demande (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_user ON notification (user_id, user_type)');
        $this->addSql('CREATE INDEX idx_lue ON notification (lue)');
        $this->addSql('CREATE INDEX idx_date ON notification (date_creation)');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476ca9b0f88b1 TO idx_activite');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476ca80e95e18 TO idx_demande');
        $this->addSql('ALTER TABLE option_variation DROP FOREIGN KEY FK_B300236A51383AF3');
        $this->addSql('DROP INDEX IDX_B300236A51383AF3 ON option_variation');
        $this->addSql('ALTER TABLE option_variation CHANGE ordre_affichage ordre_affichage INT DEFAULT 0');
        $this->addSql('CREATE UNIQUE INDEX unique_option ON option_variation (attribut_id, valeur)');
        $this->addSql('ALTER TABLE paiement CHANGE statut statut VARCHAR(50) DEFAULT \'EN_ATTENTE\', CHANGE details_json details_json TEXT DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT fk_paiement_activite FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT fk_paiement_client FOREIGN KEY (client_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT fk_paiement_demande FOREIGN KEY (demande_id) REFERENCES participation_demande (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_paiement_commande ON paiement (commande_id)');
        $this->addSql('CREATE INDEX idx_paiement_demande ON paiement (demande_id)');
        $this->addSql('CREATE INDEX idx_paiement_client ON paiement (client_id)');
        $this->addSql('CREATE INDEX idx_paiement_activite ON paiement (activite_id)');
        $this->addSql('CREATE INDEX idx_paiement_statut ON paiement (statut)');
        $this->addSql('ALTER TABLE panier CHANGE quantite quantite INT DEFAULT 1 NOT NULL, CHANGE date_ajout date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX unique_client_produit ON panier (client_id, produit_id)');
        $this->addSql('CREATE INDEX produit_id ON panier (produit_id)');
        $this->addSql('CREATE INDEX idx_variant_sku ON panier (variant_sku)');
        $this->addSql('ALTER TABLE partenaire DROP INDEX IDX_32FFA373A76ED395, ADD UNIQUE INDEX user_id (user_id)');
        $this->addSql('ALTER TABLE partenaire DROP FOREIGN KEY FK_32FFA373A76ED395');
        $this->addSql('ALTER TABLE partenaire CHANGE user_id user_id INT NOT NULL, CHANGE adresse_entreprise adresse_entreprise TEXT DEFAULT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE statut statut VARCHAR(50) DEFAULT \'EN_ATTENTE\', CHANGE commission commission NUMERIC(5, 2) DEFAULT \'10.00\', CHANGE date_inscription date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE partenaire ADD CONSTRAINT fk_partenaire_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA66649B0F88B1');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA666419EB6921');
        $this->addSql('ALTER TABLE participation_demande CHANGE activite_id activite_id INT NOT NULL, CHANGE client_id client_id INT NOT NULL, CHANGE statut statut VARCHAR(50) DEFAULT \'EN_ATTENTE\', CHANGE paiement_effectue paiement_effectue TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT fk_demande_activite FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT fk_demande_client FOREIGN KEY (client_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation_demande RENAME INDEX idx_6dea66649b0f88b1 TO idx_demande_activite_id');
        $this->addSql('ALTER TABLE participation_demande RENAME INDEX idx_6dea666419eb6921 TO idx_demande_client_id');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB75365BF48');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB7598DE13AC');
        $this->addSql('ALTER TABLE produit_parent CHANGE description description TEXT DEFAULT NULL, CHANGE materiau materiau TEXT DEFAULT NULL, CHANGE date_ajout date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE statut statut VARCHAR(255) DEFAULT \'ACTIF\'');
        $this->addSql('ALTER TABLE produit_parent RENAME INDEX idx_cadcab7598de13ac TO partenaire_id');
        $this->addSql('ALTER TABLE produit_parent RENAME INDEX idx_cadcab75365bf48 TO sous_categorie_id');
        $this->addSql('ALTER TABLE produit_variant DROP FOREIGN KEY FK_37B834D4F6ED949C');
        $this->addSql('ALTER TABLE produit_variant CHANGE quantite_stock quantite_stock INT DEFAULT 0 NOT NULL, CHANGE seuil_alerte seuil_alerte INT DEFAULT 5, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX sku ON produit_variant (sku)');
        $this->addSql('ALTER TABLE produit_variant RENAME INDEX idx_37b834d4f6ed949c TO produit_parent_id');
        $this->addSql('ALTER TABLE produits CHANGE description description TEXT DEFAULT NULL, CHANGE quantite quantite INT DEFAULT 0, CHANGE statut statut VARCHAR(255) DEFAULT \'actif\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX partenaire_id ON produits (partenaire_id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064049B0F88B1');
        $this->addSql('ALTER TABLE reclamation CHANGE client_id client_id INT NOT NULL, CHANGE activite_id activite_id INT NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE statut statut VARCHAR(50) DEFAULT \'EN_ATTENTE\', CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT reclamation_ibfk_1 FOREIGN KEY (client_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT reclamation_ibfk_2 FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamation RENAME INDEX idx_ce60640419eb6921 TO client_id');
        $this->addSql('ALTER TABLE reclamation RENAME INDEX idx_ce6064049b0f88b1 TO activite_id');
        $this->addSql('ALTER TABLE sous_categorie CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX categorie_id ON sous_categorie (categorie_id)');
        $this->addSql('ALTER TABLE users CHANGE tentative tentative INT DEFAULT 0 NOT NULL, CHANGE validation validation TINYINT(1) DEFAULT 1 NOT NULL, CHANGE block_until block_until BIGINT DEFAULT 0 NOT NULL, CHANGE block_level block_level INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX finger_id ON users (finger_id)');
        $this->addSql('ALTER TABLE utilisation_code_promo CHANGE date_utilisation date_utilisation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE utilisation_code_promo ADD CONSTRAINT utilisation_code_promo_ibfk_1 FOREIGN KEY (code_promo_id) REFERENCES code_promo (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_client ON utilisation_code_promo (client_id)');
        $this->addSql('CREATE INDEX idx_code_promo ON utilisation_code_promo (code_promo_id)');
        $this->addSql('CREATE INDEX idx_commande ON utilisation_code_promo (commande_id)');
        $this->addSql('ALTER TABLE variant_option MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7663B69A9AF');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA76651383AF3');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7669DA8FC14');
        $this->addSql('DROP INDEX IDX_4FDCA7663B69A9AF ON variant_option');
        $this->addSql('DROP INDEX IDX_4FDCA76651383AF3 ON variant_option');
        $this->addSql('DROP INDEX IDX_4FDCA7669DA8FC14 ON variant_option');
        $this->addSql('DROP INDEX `PRIMARY` ON variant_option');
        $this->addSql('ALTER TABLE variant_option ADD option_id INT NOT NULL, DROP id, DROP attribut_id, DROP option_variation_id, CHANGE variant_id variant_id INT NOT NULL');
        $this->addSql('CREATE INDEX option_id ON variant_option (option_id)');
        $this->addSql('ALTER TABLE variant_option ADD PRIMARY KEY (variant_id, option_id)');
    }
}
