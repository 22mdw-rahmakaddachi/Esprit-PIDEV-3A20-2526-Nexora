<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415215059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_notification (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT NOT NULL, commande_id INT NOT NULL, client_nom VARCHAR(100) NOT NULL, client_email VARCHAR(100) DEFAULT NULL, details LONGTEXT NOT NULL, montant NUMERIC(10, 2) NOT NULL, lue TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite CHANGE partenaire_id partenaire_id INT NOT NULL, CHANGE date_activite date_activite DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE code_promo CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_item CHANGE produit_nom produit_nom VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message_prive CHANGE sent_at sent_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA80E95E18');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9B0F88B1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CA9B0F88B1 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA80E95E18 ON notification');
        $this->addSql('DROP INDEX FK_BF5476CAA76ED395 ON notification');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE panier CHANGE date_ajout date_ajout DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE date_inscription date_inscription DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande DROP FOREIGN KEY FK_6DEA666419EB6921');
        $this->addSql('DROP INDEX IDX_6DEA666419EB6921 ON participation_demande');
        $this->addSql('ALTER TABLE participation_demande CHANGE activite_id activite_id INT NOT NULL');
        $this->addSql('ALTER TABLE produits CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_52743D7BBCF5E72D ON sous_categorie (categorie_id)');
        $this->addSql('ALTER TABLE user_warnings CHANGE last_warning_at last_warning_at DATETIME DEFAULT NULL, CHANGE blocked_at blocked_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commande_notification');
        $this->addSql('ALTER TABLE activite CHANGE partenaire_id partenaire_id INT DEFAULT NULL, CHANGE date_activite date_activite DATETIME NOT NULL, CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE code_promo CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_item CHANGE produit_nom produit_nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE created_at created_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message_prive CHANGE sent_at sent_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE user_id user_id INT DEFAULT NULL, CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA80E95E18 FOREIGN KEY (demande_id) REFERENCES participation_demande (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BF5476CA9B0F88B1 ON notification (activite_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA80E95E18 ON notification (demande_id)');
        $this->addSql('CREATE INDEX FK_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('ALTER TABLE option_variation DROP FOREIGN KEY FK_B300236A51383AF3');
        $this->addSql('ALTER TABLE panier CHANGE date_ajout date_ajout TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE date_inscription date_inscription TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande CHANGE activite_id activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_demande ADD CONSTRAINT FK_6DEA666419EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6DEA666419EB6921 ON participation_demande (client_id)');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB75365BF48');
        $this->addSql('ALTER TABLE produit_parent DROP FOREIGN KEY FK_CADCAB7598DE13AC');
        $this->addSql('ALTER TABLE produit_variant DROP FOREIGN KEY FK_37B834D4F6ED949C');
        $this->addSql('ALTER TABLE produits CHANGE created_at created_at TIME DEFAULT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE date_creation date_creation TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sous_categorie DROP FOREIGN KEY FK_52743D7BBCF5E72D');
        $this->addSql('DROP INDEX IDX_52743D7BBCF5E72D ON sous_categorie');
        $this->addSql('ALTER TABLE user_warnings CHANGE last_warning_at last_warning_at TIME DEFAULT NULL, CHANGE blocked_at blocked_at TIME DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74 ON users');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7663B69A9AF');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA76651383AF3');
        $this->addSql('ALTER TABLE variant_option DROP FOREIGN KEY FK_4FDCA7669DA8FC14');
    }
}
