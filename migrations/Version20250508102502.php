<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508102502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE commune (id INT AUTO_INCREMENT NOT NULL, wilaya_id INT NOT NULL, nom VARCHAR(100) NOT NULL, INDEX IDX_E2E2D1EEDC89F5B6 (wilaya_id), UNIQUE INDEX UNIQ_E2E2D1EE6C6E55B5DC89F5B6 (nom, wilaya_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tarif (id INT AUTO_INCREMENT NOT NULL, origine_wilaya_id INT NOT NULL, origine_commune_id INT DEFAULT NULL, destination_wilaya_id INT NOT NULL, destination_commune_id INT DEFAULT NULL, mode VARCHAR(255) DEFAULT 'domicile' NOT NULL, urgence VARCHAR(255) DEFAULT 'standard' NOT NULL, poids_min NUMERIC(6, 2) NOT NULL, poids_max NUMERIC(6, 2) NOT NULL, prix NUMERIC(10, 2) NOT NULL, INDEX IDX_E7189C9E20B1D7E (origine_wilaya_id), INDEX IDX_E7189C9869B8718 (origine_commune_id), INDEX IDX_E7189C98D09454C (destination_wilaya_id), INDEX IDX_E7189C94E23D4C0 (destination_commune_id), UNIQUE INDEX UNIQ_E7189C9E20B1D7E869B87188D09454C4E23D4C097CA47AB737D6BCDE10 (origine_wilaya_id, origine_commune_id, destination_wilaya_id, destination_commune_id, mode, urgence, poids_min, poids_max), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE wilaya (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_CF6AF33B6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EEDC89F5B6 FOREIGN KEY (wilaya_id) REFERENCES wilaya (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9E20B1D7E FOREIGN KEY (origine_wilaya_id) REFERENCES wilaya (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9869B8718 FOREIGN KEY (origine_commune_id) REFERENCES commune (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C98D09454C FOREIGN KEY (destination_wilaya_id) REFERENCES wilaya (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif ADD CONSTRAINT FK_E7189C94E23D4C0 FOREIGN KEY (destination_commune_id) REFERENCES commune (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commune DROP FOREIGN KEY FK_E2E2D1EEDC89F5B6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9E20B1D7E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9869B8718
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C98D09454C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C94E23D4C0
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commune
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tarif
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE wilaya
        SQL);
    }
}
