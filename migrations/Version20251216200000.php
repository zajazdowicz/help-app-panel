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
        // Check if user_id column already exists
        $table = $schema->getTable('dream_fulfillments');
        if (!$table->hasColumn('user_id')) {
            $this->addSql('ALTER TABLE dream_fulfillments ADD user_id INT DEFAULT NULL');
        }
        // Check if foreign key already exists
        $foreignKeys = $table->getForeignKeys();
        $fkExists = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->getColumns() === ['user_id'] && $fk->getForeignTableName() === 'users') {
                $fkExists = true;
                break;
            }
        }
        if (!$fkExists) {
            $this->addSql('ALTER TABLE dream_fulfillments ADD CONSTRAINT FK_DREAM_FULFILLMENTS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        }
        // Check if index already exists
        $indexes = $table->getIndexes();
        $idxExists = false;
        foreach ($indexes as $index) {
            if ($index->getColumns() === ['user_id'] && $index->getName() !== 'primary') {
                $idxExists = true;
                break;
            }
        }
        if (!$idxExists) {
            $this->addSql('CREATE INDEX IDX_DREAM_FULFILLMENTS_USER ON dream_fulfillments (user_id)');
        }
        
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
