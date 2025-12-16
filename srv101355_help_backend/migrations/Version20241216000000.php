<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial tables for HelpDreams project';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE children (id INT AUTO_INCREMENT NOT NULL, orphanage_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, age INT NOT NULL, description VARCHAR(100) NOT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FFC0B4AEFB5C6C57 (orphanage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dream_fulfillments (id INT AUTO_INCREMENT NOT NULL, dream_id INT NOT NULL, donor_name VARCHAR(255) DEFAULT NULL, donor_email VARCHAR(255) DEFAULT NULL, donor_nickname VARCHAR(100) DEFAULT NULL, is_anonymous TINYINT(1) NOT NULL, status VARCHAR(20) NOT NULL, quantity_fulfilled INT NOT NULL, child_photo_url VARCHAR(500) DEFAULT NULL, child_message VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_ABC123DEF456 (dream_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dreams (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, orphanage_id INT NOT NULL, product_url VARCHAR(500) NOT NULL, product_title VARCHAR(255) NOT NULL, product_price NUMERIC(10, 2) NOT NULL, product_category VARCHAR(100) NOT NULL, description VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, quantity_needed INT NOT NULL, quantity_fulfilled INT NOT NULL, is_urgent TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_789012ABC456 (child_id), INDEX IDX_789012DEF789 (orphanage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orphanages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, region VARCHAR(100) NOT NULL, postal_code VARCHAR(20) NOT NULL, contact_email VARCHAR(180) NOT NULL, contact_phone VARCHAR(30) NOT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, orphanage_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9FB5C6C57 (orphanage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE children ADD CONSTRAINT FK_FFC0B4AEFB5C6C57 FOREIGN KEY (orphanage_id) REFERENCES orphanages (id)');
        $this->addSql('ALTER TABLE dream_fulfillments ADD CONSTRAINT FK_ABC123DEF456 FOREIGN KEY (dream_id) REFERENCES dreams (id)');
        $this->addSql('ALTER TABLE dreams ADD CONSTRAINT FK_789012ABC456 FOREIGN KEY (child_id) REFERENCES children (id)');
        $this->addSql('ALTER TABLE dreams ADD CONSTRAINT FK_789012DEF789 FOREIGN KEY (orphanage_id) REFERENCES orphanages (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9FB5C6C57 FOREIGN KEY (orphanage_id) REFERENCES orphanages (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE children DROP FOREIGN KEY FK_FFC0B4AEFB5C6C57');
        $this->addSql('ALTER TABLE dream_fulfillments DROP FOREIGN KEY FK_ABC123DEF456');
        $this->addSql('ALTER TABLE dreams DROP FOREIGN KEY FK_789012ABC456');
        $this->addSql('ALTER TABLE dreams DROP FOREIGN KEY FK_789012DEF789');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9FB5C6C57');
        $this->addSql('DROP TABLE children');
        $this->addSql('DROP TABLE dream_fulfillments');
        $this->addSql('DROP TABLE dreams');
        $this->addSql('DROP TABLE orphanages');
        $this->addSql('DROP TABLE users');
    }
}
