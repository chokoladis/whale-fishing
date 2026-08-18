<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816194604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix coin_detail';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_detail ADD volume24 NUMERIC(24, 10) DEFAULT NULL');
        $this->addSql('ALTER TABLE coin_detail ALTER volume DROP NOT NULL');
        $this->addSql('ALTER TABLE coin_detail ALTER market_cap TYPE NUMERIC(30, 8)');
        $this->addSql('ALTER TABLE wallet_coin ALTER balance TYPE NUMERIC(56, 26)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_detail DROP volume24');
        $this->addSql('ALTER TABLE coin_detail ALTER volume SET NOT NULL');
        $this->addSql('ALTER TABLE coin_detail ALTER market_cap TYPE NUMERIC(18, 4)');
    }
}
