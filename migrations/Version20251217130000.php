<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Category entity and replace product_category with relation';
    }

    public function up(Schema $schema): void
    {
        // 1. Create categories table
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 2. Add category_id column to dreams table
        $this->addSql('ALTER TABLE dreams ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dreams ADD CONSTRAINT FK_6B5F276A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_6B5F276A12469DE2 ON dreams (category_id)');

        // 3. Insert default categories
        $this->addSql("INSERT INTO categories (name, slug, is_active, created_at) VALUES
            ('Sport', 'sport', 1, NOW()),
            ('Edukacja', 'edukacja', 1, NOW()),
            ('Zabawki', 'zabawki', 1, NOW()),
            ('Elektronika', 'elektronika', 1, NOW()),
            ('Książki', 'ksiazki', 1, NOW()),
            ('Moda', 'moda', 1, NOW()),
            ('Sztuka', 'sztuka', 1, NOW()),
            ('Inne', 'inne', 1, NOW())
        ");

        // 4. Update existing dreams: assign a default category (e.g., 'Inne' with id 8)
        $this->addSql('UPDATE dreams SET category_id = 8 WHERE category_id IS NULL');

        // 5. Make category_id NOT NULL after data is populated
        $this->addSql('ALTER TABLE dreams CHANGE category_id category_id INT NOT NULL');

        // 6. Drop old product_category column
        $this->addSql('ALTER TABLE dreams DROP product_category');
    }

    public function down(Schema $schema): void
    {
        // 1. Add back product_category column
        $this->addSql('ALTER TABLE dreams ADD product_category VARCHAR(100) DEFAULT NULL');

        // 2. Restore product_category from category name (simplified – we'll set all to 'Inne')
        $this->addSql('UPDATE dreams d INNER JOIN categories c ON d.category_id = c.id SET d.product_category = c.name');

        // 3. Remove foreign key and drop category_id
        $this->addSql('ALTER TABLE dreams DROP FOREIGN KEY FK_6B5F276A12469DE2');
        $this->addSql('DROP INDEX IDX_6B5F276A12469DE2 ON dreams');
        $this->addSql('ALTER TABLE dreams DROP category_id');

        // 4. Drop categories table
        $this->addSql('DROP TABLE categories');
    }
}
