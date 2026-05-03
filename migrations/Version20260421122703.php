<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421122703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_commentaire DROP FOREIGN KEY FK_PUB_COMM');
        $this->addSql('ALTER TABLE publication_reaction DROP FOREIGN KEY FK_PUB_REAC');
        $this->addSql('DROP TABLE publication_commentaire');
        $this->addSql('DROP TABLE publication_reaction');
        $this->addSql('ALTER TABLE activite CHANGE partenaire_id partenaire_id INT NOT NULL, CHANGE date_activite date_activite DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE code_promo CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_item CHANGE produit_nom produit_nom VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE destination_avis DROP FOREIGN KEY FK_DEST_AVIS_DEST');
        $this->addSql('ALTER TABLE destination_avis DROP FOREIGN KEY FK_DEST_AVIS_USER');
        $this->addSql('ALTER TABLE destination_avis ADD CONSTRAINT FK_2C3022C1816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id)');
        $this->addSql('ALTER TABLE destination_avis ADD CONSTRAINT FK_2C3022C1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE destination_avis RENAME INDEX idx_dest_avis_dest TO IDX_2C3022C1816C6140');
        $this->addSql('ALTER TABLE destination_avis RENAME INDEX idx_dest_avis_user TO IDX_2C3022C1A76ED395');
        $this->addSql('ALTER TABLE destination_avis_image RENAME INDEX idx_dest_avis_img TO IDX_EFCC1E93197E709F');
        $this->addSql('ALTER TABLE destination_image CHANGE ordre ordre INT DEFAULT NULL');
        $this->addSql('ALTER TABLE destination_image RENAME INDEX idx_dest_img TO IDX_9440A5EC816C6140');
        $this->addSql('ALTER TABLE destination_message CHANGE destination_id destination_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE destination_message RENAME INDEX idx_dest_msg TO IDX_D8840F07816C6140');
        $this->addSql('ALTER TABLE destination_participant CHANGE destination_id destination_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE destination_participant RENAME INDEX idx_dest_part TO IDX_F2AD802A816C6140');
        $this->addSql('ALTER TABLE fingerprint ADD CONSTRAINT FK_FC0B754AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE message_prive CHANGE sent_at sent_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA80E95E18');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9B0F88B1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CA80E95E18 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA9B0F88B1 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CAA76ED395 ON notification');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE offre CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE option_variation ADD CONSTRAINT FK_B300236A51383AF3 FOREIGN KEY (attribut_id) REFERENCES attribut_variation (id)');
        $this->addSql('CREATE INDEX IDX_B300236A51383AF3 ON option_variation (attribut_id)');
        $this->addSql('ALTER TABLE panier CHANGE date_ajout date_ajout DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE date_inscription date_inscription DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA666419EB6921');
        $this->addSql('DROP INDEX IDX_6DEA666419EB6921 ON participation_demande');
        $this->addSql('ALTER TABLE participation_demande CHANGE activite_id activite_id INT NOT NULL, CHANGE client_id client_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit_parent CHANGE date_ajout date_ajout DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE produit_parent ADD CONSTRAINT FK_CADCAB75365BF48 FOREIGN KEY (sous_categorie_id) REFERENCES sous_categorie (id)');
        $this->addSql('ALTER TABLE produit_parent ADD CONSTRAINT FK_CADCAB7598DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (id)');
        $this->addSql('CREATE INDEX IDX_CADCAB75365BF48 ON produit_parent (sous_categorie_id)');
        $this->addSql('CREATE INDEX IDX_CADCAB7598DE13AC ON produit_parent (partenaire_id)');
        $this->addSql('ALTER TABLE produit_variant CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE produit_variant ADD CONSTRAINT FK_37B834D4F6ED949C FOREIGN KEY (produit_parent_id) REFERENCES produit_parent (id)');
        $this->addSql('CREATE INDEX IDX_37B834D4F6ED949C ON produit_variant (produit_parent_id)');
        $this->addSql('ALTER TABLE produits CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE publication CHANGE auteur auteur VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sous_categorie ADD CONSTRAINT FK_52743D7BBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_52743D7BBCF5E72D ON sous_categorie (categorie_id)');
        $this->addSql('ALTER TABLE user_warnings CHANGE last_warning_at last_warning_at DATETIME DEFAULT NULL, CHANGE blocked_at blocked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisation_code_promo CHANGE date_utilisation date_utilisation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_VO_ATTRIBUT');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_VO_OPTION');
        $this->addSql('ALTER TABLE variant_option ADD CONSTRAINT FK_4FDCA76651383AF3 FOREIGN KEY (attribut_id) REFERENCES attribut_variation (id)');
        $this->addSql('ALTER TABLE variant_option ADD CONSTRAINT FK_4FDCA7669DA8FC14 FOREIGN KEY (option_variation_id) REFERENCES option_variation (id)');
        $this->addSql('ALTER TABLE variant_option RENAME INDEX fk_vo_attribut TO IDX_4FDCA76651383AF3');
        $this->addSql('ALTER TABLE variant_option RENAME INDEX fk_vo_option TO IDX_4FDCA7669DA8FC14');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_commentaire (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, auteur VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, contenu LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX IDX_PUB_COMM (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE publication_reaction (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, auteur VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type_reaction VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX IDX_PUB_REAC (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE publication_commentaire ADD CONSTRAINT FK_PUB_COMM FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_reaction ADD CONSTRAINT FK_PUB_REAC FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite CHANGE date_activite date_activite DATETIME NOT NULL, CHANGE created_at created_at TIME DEFAULT NULL, CHANGE partenaire_id partenaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE code_promo CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_item CHANGE produit_nom produit_nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE destination_avis DROP FOREIGN KEY FK_2C3022C1816C6140');
        $this->addSql('ALTER TABLE destination_avis DROP FOREIGN KEY FK_2C3022C1A76ED395');
        $this->addSql('ALTER TABLE destination_avis ADD CONSTRAINT FK_DEST_AVIS_DEST FOREIGN KEY (destination_id) REFERENCES destination (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE destination_avis ADD CONSTRAINT FK_DEST_AVIS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE destination_avis RENAME INDEX idx_2c3022c1816c6140 TO IDX_DEST_AVIS_DEST');
        $this->addSql('ALTER TABLE destination_avis RENAME INDEX idx_2c3022c1a76ed395 TO IDX_DEST_AVIS_USER');
        $this->addSql('ALTER TABLE destination_avis_image RENAME INDEX idx_efcc1e93197e709f TO IDX_DEST_AVIS_IMG');
        $this->addSql('ALTER TABLE destination_image CHANGE ordre ordre INT DEFAULT 0');
        $this->addSql('ALTER TABLE destination_image RENAME INDEX idx_9440a5ec816c6140 TO IDX_DEST_IMG');
        $this->addSql('ALTER TABLE destination_message CHANGE destination_id destination_id INT NOT NULL');
        $this->addSql('ALTER TABLE destination_message RENAME INDEX idx_d8840f07816c6140 TO IDX_DEST_MSG');
        $this->addSql('ALTER TABLE destination_participant CHANGE destination_id destination_id INT NOT NULL');
        $this->addSql('ALTER TABLE destination_participant RENAME INDEX idx_f2ad802a816c6140 TO IDX_DEST_PART');
        $this->addSql('ALTER TABLE fingerprint DROP FOREIGN KEY FK_FC0B754AA76ED395');
        $this->addSql('ALTER TABLE message_prive CHANGE sent_at sent_at TIME DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT DEFAULT NULL, CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA80E95E18 FOREIGN KEY (demande_id) REFERENCES participation_demande (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BF5476CA80E95E18 ON notification (demande_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA9B0F88B1 ON notification (activite_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('ALTER TABLE offre CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE option_variation DROP FOREIGN KEY FK_B300236A51383AF3');
        $this->addSql('DROP INDEX IDX_B300236A51383AF3 ON option_variation');
        $this->addSql('ALTER TABLE panier CHANGE date_ajout date_ajout TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE date_inscription date_inscription TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande CHANGE client_id client_id INT DEFAULT NULL, CHANGE activite_id activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA666419EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6DEA666419EB6921 ON participation_demande (client_id)');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB75365BF48');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB7598DE13AC');
        $this->addSql('DROP INDEX IDX_CADCAB75365BF48 ON produit_parent');
        $this->addSql('DROP INDEX IDX_CADCAB7598DE13AC ON produit_parent');
        $this->addSql('ALTER TABLE produit_parent CHANGE date_ajout date_ajout TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE produit_variant DROP FOREIGN KEY FK_37B834D4F6ED949C');
        $this->addSql('DROP INDEX IDX_37B834D4F6ED949C ON produit_variant');
        $this->addSql('ALTER TABLE produit_variant CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE produits CHANGE created_at created_at TIME DEFAULT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE publication CHANGE auteur auteur VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sous_categorie DROP FOREIGN KEY FK_52743D7BBCF5E72D');
        $this->addSql('DROP INDEX IDX_52743D7BBCF5E72D ON sous_categorie');
        $this->addSql('ALTER TABLE user_warnings CHANGE last_warning_at last_warning_at TIME DEFAULT NULL, CHANGE blocked_at blocked_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisation_code_promo CHANGE date_utilisation date_utilisation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA76651383AF3');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7669DA8FC14');
        $this->addSql('ALTER TABLE variant_option ADD CONSTRAINT FK_VO_ATTRIBUT FOREIGN KEY (attribut_id) REFERENCES attribut_variation (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE variant_option ADD CONSTRAINT FK_VO_OPTION FOREIGN KEY (option_variation_id) REFERENCES option_variation (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE variant_option RENAME INDEX idx_4fdca76651383af3 TO FK_VO_ATTRIBUT');
        $this->addSql('ALTER TABLE variant_option RENAME INDEX idx_4fdca7669da8fc14 TO FK_VO_OPTION');
    }
}
