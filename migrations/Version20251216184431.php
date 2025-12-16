<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216184431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dream_fulfillments CHANGE donor_name donor_name VARCHAR(255) DEFAULT NULL, CHANGE donor_email donor_email VARCHAR(255) DEFAULT NULL, CHANGE donor_nickname donor_nickname VARCHAR(100) DEFAULT NULL, CHANGE child_photo_url child_photo_url VARCHAR(500) DEFAULT NULL, CHANGE child_message child_message VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE dream_fulfillments CHANGE donor_name donor_name VARCHAR(255) DEFAULT \'NULL\', CHANGE donor_email donor_email VARCHAR(255) DEFAULT \'NULL\', CHANGE donor_nickname donor_nickname VARCHAR(100) DEFAULT \'NULL\', CHANGE child_photo_url child_photo_url VARCHAR(500) DEFAULT \'NULL\', CHANGE child_message child_message VARCHAR(500) DEFAULT \'NULL\'');
    }
}
