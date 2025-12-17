<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user relation to dream_fulfillments table';
    }

    public function up(Schema $schema): void
    {
        // Check if the column already exists
        $table = $schema->getTable('dream_fulfillments');
        if (!$table->hasColumn('user_id')) {
            $this->addSql('ALTER TABLE dream_fulfillments ADD user_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE dream_fulfillments ADD CONSTRAINT FK_DREAM_FULFILLMENTS_USER FOREIGN KEY (user_id) REFERENCES users (id)');
            $this->addSql('CREATE INDEX IDX_DREAM_FULFILLMENTS_USER ON dream_fulfillments (user_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('dream_fulfillments');
        if ($table->hasColumn('user_id')) {
            $this->addSql('ALTER TABLE dream_fulfillments DROP FOREIGN KEY FK_DREAM_FULFILLMENTS_USER');
            $this->addSql('DROP INDEX IDX_DREAM_FULFILLMENTS_USER ON dream_fulfillments');
            $this->addSql('ALTER TABLE dream_fulfillments DROP COLUMN user_id');
        }
    }
}
