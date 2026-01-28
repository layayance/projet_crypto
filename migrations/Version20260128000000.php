<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create crypto_asset table for portfolio management';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto_asset (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, symbol VARCHAR(10) NOT NULL, name VARCHAR(100) NOT NULL, quantity NUMERIC(20, 8) NOT NULL, purchase_price NUMERIC(20, 2) NOT NULL, purchase_date DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CRYPTO_ASSET_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crypto_asset ADD CONSTRAINT FK_CRYPTO_ASSET_USER FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crypto_asset DROP FOREIGN KEY FK_CRYPTO_ASSET_USER');
        $this->addSql('DROP TABLE crypto_asset');
    }
}
