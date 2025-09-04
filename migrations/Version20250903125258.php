<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903125258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe_prive (id INT AUTO_INCREMENT NOT NULL, chef_groupe_id INT DEFAULT NULL, nom VARCHAR(55) NOT NULL, UNIQUE INDEX UNIQ_A8D00A9D6C6E55B5 (nom), INDEX IDX_A8D00A9DA9346182 (chef_groupe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe_prive_user (groupe_prive_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_7DCAA646EFB6D465 (groupe_prive_id), INDEX IDX_7DCAA646A76ED395 (user_id), PRIMARY KEY(groupe_prive_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groupe_prive ADD CONSTRAINT FK_A8D00A9DA9346182 FOREIGN KEY (chef_groupe_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE groupe_prive_user ADD CONSTRAINT FK_7DCAA646EFB6D465 FOREIGN KEY (groupe_prive_id) REFERENCES groupe_prive (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe_prive_user ADD CONSTRAINT FK_7DCAA646A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie ADD groupe_prive_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2EFB6D465 FOREIGN KEY (groupe_prive_id) REFERENCES groupe_prive (id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2EFB6D465 ON sortie (groupe_prive_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2EFB6D465');
        $this->addSql('ALTER TABLE groupe_prive DROP FOREIGN KEY FK_A8D00A9DA9346182');
        $this->addSql('ALTER TABLE groupe_prive_user DROP FOREIGN KEY FK_7DCAA646EFB6D465');
        $this->addSql('ALTER TABLE groupe_prive_user DROP FOREIGN KEY FK_7DCAA646A76ED395');
        $this->addSql('DROP TABLE groupe_prive');
        $this->addSql('DROP TABLE groupe_prive_user');
        $this->addSql('DROP INDEX IDX_3C3FD3F2EFB6D465 ON sortie');
        $this->addSql('ALTER TABLE sortie DROP groupe_prive_id');
    }
}
