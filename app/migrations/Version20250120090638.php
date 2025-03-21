<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250120090638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture CHANGE gite_id gite_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495541DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id)');
        $this->addSql('CREATE INDEX IDX_42C8495541DEE7B9 ON reservation (token_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture CHANGE gite_id gite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495541DEE7B9');
        $this->addSql('DROP INDEX IDX_42C8495541DEE7B9 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP token_id');
    }
}
