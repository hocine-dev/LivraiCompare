<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511154819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C94E23D4C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9869B8718
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_E7189C9E20B1D7E869B87188D09454C4E23D4C097CA47AB737D6BCDE10 ON tarif
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E7189C9869B8718 ON tarif
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E7189C94E23D4C0 ON tarif
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP origine_commune_id, DROP destination_commune_id, DROP mode, DROP urgence, DROP poids_min, DROP poids_max, DROP prix_colis_min, DROP prix_colis_max, DROP delai_heures
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_E7189C9E20B1D7E8D09454CFCF77503 ON tarif (origine_wilaya_id, destination_wilaya_id, societe_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_E7189C9E20B1D7E8D09454CFCF77503 ON tarif
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD origine_commune_id INT DEFAULT NULL, ADD destination_commune_id INT DEFAULT NULL, ADD mode VARCHAR(255) DEFAULT 'domicile' NOT NULL, ADD urgence VARCHAR(255) DEFAULT 'standard' NOT NULL, ADD poids_min NUMERIC(6, 2) NOT NULL, ADD poids_max NUMERIC(6, 2) NOT NULL, ADD prix_colis_min NUMERIC(10, 2) NOT NULL, ADD prix_colis_max NUMERIC(10, 2) NOT NULL, ADD delai_heures INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C94E23D4C0 FOREIGN KEY (destination_commune_id) REFERENCES commune (id) ON UPDATE NO ACTION ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9869B8718 FOREIGN KEY (origine_commune_id) REFERENCES commune (id) ON UPDATE NO ACTION ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_E7189C9E20B1D7E869B87188D09454C4E23D4C097CA47AB737D6BCDE10 ON tarif (origine_wilaya_id, origine_commune_id, destination_wilaya_id, destination_commune_id, mode, urgence, poids_min, poids_max, societe_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7189C9869B8718 ON tarif (origine_commune_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7189C94E23D4C0 ON tarif (destination_commune_id)
        SQL);
    }
}
