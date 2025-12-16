<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add missing username column and mark Version20251216183356 as executed.
 */
final class Version20251216220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add username column to users table and fix migration state';
    }

    public function up(Schema $schema): void
    {
        // Check if username column exists in users table
        $table = $schema->getTable('users');
        if (!$table->hasColumn('username')) {
            $this->addSql('ALTER TABLE users ADD username VARCHAR(180) NOT NULL');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        }

        // Mark Version20251216183356 as executed to avoid duplicate table creation
        $this->addSql("INSERT IGNORE INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\\\Version20251216183356', NOW(), 0)");
    }

    public function down(Schema $schema): void
    {
        // Remove the username column
        $this->addSql('DROP INDEX UNIQ_1483A5E9F85E0677 ON users');
        $this->addSql('ALTER TABLE users DROP COLUMN username');

        // Remove the migration record
        $this->addSql("DELETE FROM doctrine_migration_versions WHERE version = 'DoctrineMigrations\\\\Version20251216183356'");
    }
}
