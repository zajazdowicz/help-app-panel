<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251217010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add photo_url to children and amount to dream_fulfillments';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE children ADD photo_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dream_fulfillments ADD amount NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE children DROP photo_url');
        $this->addSql('ALTER TABLE dream_fulfillments DROP amount');
    }
}
