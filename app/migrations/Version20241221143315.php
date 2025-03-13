<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221143315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, gite_id INT DEFAULT NULL, category_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, INDEX IDX_16DB4F89652CAE9B (gite_id), INDEX IDX_16DB4F8912469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, gite_id INT DEFAULT NULL, creation_date DATETIME NOT NULL, rating INT NOT NULL, comment LONGTEXT NOT NULL, is_verified TINYINT(1) NOT NULL, response LONGTEXT NOT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C6652CAE9B (gite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE supplement (id INT AUTO_INCREMENT NOT NULL, gite_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date_supplement DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_15A73C9652CAE9B (gite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, gite_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, discount DOUBLE PRECISION NOT NULL, is_active TINYINT(1) NOT NULL, expiration_date DATETIME NOT NULL, INDEX IDX_5F37A13B652CAE9B (gite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        // $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        // $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('ALTER TABLE supplement ADD CONSTRAINT FK_15A73C9652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13B652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('ALTER TABLE gite ADD address VARCHAR(50) NOT NULL, ADD cp VARCHAR(50) NOT NULL, ADD capacity INT NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD price DOUBLE PRECISION NOT NULL, ADD cleaning_charge DOUBLE PRECISION NOT NULL');
        // $this->addSql('ALTER TABLE period ADD gite_id INT DEFAULT NULL, ADD start_date DATETIME NOT NULL, ADD end_date DATETIME NOT NULL, ADD supplement DOUBLE PRECISION NOT NULL');
        // $this->addSql('ALTER TABLE period ADD CONSTRAINT FK_C5B81ECE652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('CREATE INDEX IDX_C5B81ECE652CAE9B ON period (gite_id)');
        // $this->addSql('ALTER TABLE reservation ADD gite_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, ADD reference VARCHAR(255) NOT NULL, ADD reservation_date DATETIME NOT NULL, ADD arrival_date DATETIME NOT NULL, ADD departure_date DATETIME NOT NULL, ADD last_name VARCHAR(50) NOT NULL, ADD first_name VARCHAR(50) NOT NULL, ADD address VARCHAR(50) NOT NULL, ADD cp VARCHAR(50) NOT NULL, ADD country VARCHAR(255) NOT NULL, ADD phone VARCHAR(50) NOT NULL, ADD is_major TINYINT(1) NOT NULL, ADD number_adult INT NOT NULL, ADD number_kid INT NOT NULL, ADD total_night INT NOT NULL, ADD total_price DOUBLE PRECISION NOT NULL, ADD tourism_tax DOUBLE PRECISION NOT NULL, ADD tva DOUBLE PRECISION NOT NULL, ADD is_confirm JSON NOT NULL');
        // $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        // $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        // $this->addSql('CREATE INDEX IDX_42C84955652CAE9B ON reservation (gite_id)');
        // $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F89652CAE9B');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8912469DE2');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6652CAE9B');
        $this->addSql('ALTER TABLE supplement DROP FOREIGN KEY FK_15A73C9652CAE9B');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13B652CAE9B');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE supplement');
        $this->addSql('DROP TABLE token');
        $this->addSql('ALTER TABLE gite DROP address, DROP cp, DROP capacity, DROP description, DROP price, DROP cleaning_charge');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955652CAE9B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('DROP INDEX IDX_42C84955652CAE9B ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP gite_id, DROP user_id, DROP reference, DROP reservation_date, DROP arrival_date, DROP departure_date, DROP last_name, DROP first_name, DROP address, DROP cp, DROP country, DROP phone, DROP is_major, DROP number_adult, DROP number_kid, DROP total_night, DROP total_price, DROP tourism_tax, DROP tva, DROP is_confirm');
        $this->addSql('ALTER TABLE period DROP FOREIGN KEY FK_C5B81ECE652CAE9B');
        $this->addSql('DROP INDEX IDX_C5B81ECE652CAE9B ON period');
        $this->addSql('ALTER TABLE period DROP gite_id, DROP start_date, DROP end_date, DROP supplement');
    }
}
