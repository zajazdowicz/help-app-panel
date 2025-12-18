<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add affiliate tables and fields to dreams';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affiliate_clicks (id INT AUTO_INCREMENT NOT NULL, dream_id INT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, session_id VARCHAR(100) DEFAULT NULL, clicked_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_ABC123 (dream_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE affiliate_conversions (id INT AUTO_INCREMENT NOT NULL, dream_id INT NOT NULL, click_id INT DEFAULT NULL, order_id VARCHAR(100) DEFAULT NULL, amount NUMERIC(10, 2) DEFAULT NULL, commission NUMERIC(10, 2) DEFAULT NULL, quantity INT NOT NULL, converted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DEF456 (dream_id), INDEX IDX_GHI789 (click_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affiliate_clicks ADD CONSTRAINT FK_AFF_CLICK_DREAM FOREIGN KEY (dream_id) REFERENCES dreams (id)');
        $this->addSql('ALTER TABLE affiliate_conversions ADD CONSTRAINT FK_AFF_CONV_DREAM FOREIGN KEY (dream_id) REFERENCES dreams (id)');
        $this->addSql('ALTER TABLE affiliate_conversions ADD CONSTRAINT FK_AFF_CONV_CLICK FOREIGN KEY (click_id) REFERENCES affiliate_clicks (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dreams ADD affiliate_partner VARCHAR(50) DEFAULT NULL, ADD affiliate_tracking_id VARCHAR(255) DEFAULT NULL, ADD original_product_url VARCHAR(2000) DEFAULT NULL, ADD affiliate_url VARCHAR(2000) DEFAULT NULL, ADD purchased_quantity INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affiliate_clicks DROP FOREIGN KEY FK_AFF_CLICK_DREAM');
        $this->addSql('ALTER TABLE affiliate_conversions DROP FOREIGN KEY FK_AFF_CONV_DREAM');
        $this->addSql('ALTER TABLE affiliate_conversions DROP FOREIGN KEY FK_AFF_CONV_CLICK');
        $this->addSql('DROP TABLE affiliate_clicks');
        $this->addSql('DROP TABLE affiliate_conversions');
        $this->addSql('ALTER TABLE dreams DROP affiliate_partner, DROP affiliate_tracking_id, DROP original_product_url, DROP affiliate_url, DROP purchased_quantity');
    }
}
