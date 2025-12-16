<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user relation to dream_fulfillments and set default user role';
    }

    public function up(Schema $schema): void
    {
        // Add user_id column to dream_fulfillments table
        $this->addSql('ALTER TABLE dream_fulfillments ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dream_fulfillments ADD CONSTRAINT FK_DREAM_FULFILLMENTS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DREAM_FULFILLMENTS_USER ON dream_fulfillments (user_id)');
        
        // Update existing users to have ROLE_USER if roles column is empty
        $this->addSql("UPDATE users SET roles = '[\"ROLE_USER\"]' WHERE roles = '[]' OR roles IS NULL");
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key and column
        $this->addSql('ALTER TABLE dream_fulfillments DROP FOREIGN KEY FK_DREAM_FULFILLMENTS_USER');
        $this->addSql('DROP INDEX IDX_DREAM_FULFILLMENTS_USER ON dream_fulfillments');
        $this->addSql('ALTER TABLE dream_fulfillments DROP COLUMN user_id');
        
        // Note: We cannot revert the roles update, so down migration is partial
    }
}
