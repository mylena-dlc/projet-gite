<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250124202404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE extra (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation_extra (id INT AUTO_INCREMENT NOT NULL, extra_id INT DEFAULT NULL, reservation_id INT DEFAULT NULL, date DATETIME NOT NULL, quantity INT NOT NULL, INDEX IDX_E40DDC22B959FC6 (extra_id), INDEX IDX_E40DDC2B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_extra ADD CONSTRAINT FK_E40DDC22B959FC6 FOREIGN KEY (extra_id) REFERENCES extra (id)');
        $this->addSql('ALTER TABLE reservation_extra ADD CONSTRAINT FK_E40DDC2B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE supplement DROP FOREIGN KEY FK_15A73C9652CAE9B');
        $this->addSql('DROP TABLE supplement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplement (id INT AUTO_INCREMENT NOT NULL, gite_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_supplement DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_15A73C9652CAE9B (gite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE supplement ADD CONSTRAINT FK_15A73C9652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reservation_extra DROP FOREIGN KEY FK_E40DDC22B959FC6');
        $this->addSql('ALTER TABLE reservation_extra DROP FOREIGN KEY FK_E40DDC2B83297E7');
        $this->addSql('DROP TABLE extra');
        $this->addSql('DROP TABLE reservation_extra');
    }
}
