<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327193958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE gite ADD city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD stripe_payment_id VARCHAR(255) DEFAULT NULL, CHANGE is_confirm is_confirm JSON DEFAULT NULL, CHANGE message message LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6652CAE9B');
        $this->addSql('DROP INDEX IDX_794381C6652CAE9B ON review');
        $this->addSql('ALTER TABLE review ADD title VARCHAR(50) NOT NULL, CHANGE creation_date creation_date DATETIME DEFAULT NULL, CHANGE response response LONGTEXT DEFAULT NULL, CHANGE gite_id reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_794381C6B83297E7 ON review (reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B83297E7');
        $this->addSql('DROP INDEX IDX_794381C6B83297E7 ON review');
        $this->addSql('ALTER TABLE review DROP title, CHANGE creation_date creation_date DATETIME NOT NULL, CHANGE response response LONGTEXT NOT NULL, CHANGE reservation_id gite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_794381C6652CAE9B ON review (gite_id)');
        $this->addSql('ALTER TABLE reservation DROP stripe_payment_id, CHANGE is_confirm is_confirm JSON NOT NULL, CHANGE message message LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE gite DROP city');
    }
}
