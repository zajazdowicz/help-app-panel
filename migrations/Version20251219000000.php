<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove donation system and keep only affiliate tracking';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS dream_fulfillments');
        $this->addSql('ALTER TABLE dreams DROP COLUMN IF EXISTS quantity_fulfilled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dream_fulfillments (id INT AUTO_INCREMENT NOT NULL, dream_id INT DEFAULT NULL, donor_name VARCHAR(255) DEFAULT NULL, donor_email VARCHAR(255) DEFAULT NULL, donor_nickname VARCHAR(255) DEFAULT NULL, is_anonymous TINYINT(1) NOT NULL, quantity_fulfilled INT NOT NULL, amount NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(255) NOT NULL, child_photo_url VARCHAR(500) DEFAULT NULL, child_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1234567890abcdef (dream_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dream_fulfillments ADD CONSTRAINT FK_1234567890abcdef FOREIGN KEY (dream_id) REFERENCES dreams (id)');
        $this->addSql('ALTER TABLE dreams ADD quantity_fulfilled INT NOT NULL DEFAULT 0');
    }
}
