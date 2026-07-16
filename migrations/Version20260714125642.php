<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260714125642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix type columns in coin_detail ';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin ALTER avg_price TYPE VARCHAR(60)');
        $this->addSql('DROP INDEX idx_cointcontract_contractaddress_network');
        $this->addSql('ALTER TABLE coin_contract ALTER local_price TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE coin_detail ALTER liquidity TYPE NUMERIC(26, 10)');
        $this->addSql('ALTER TABLE coin_detail ALTER volume TYPE NUMERIC(24, 10)');
        $this->addSql('ALTER TABLE coin_detail ALTER total_supply TYPE NUMERIC(16, 0)');
        $this->addSql('ALTER TABLE coin_detail ALTER circulation_supply TYPE NUMERIC(26, 10)');
        $this->addSql('ALTER TABLE coin_detail ADD max_supply BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE coin_detail DROP listed_at');
        $this->addSql('ALTER TABLE coin_detail ALTER market_cap TYPE NUMERIC(24, 4)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin ALTER avg_price TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE coin_contract ALTER local_price TYPE DOUBLE PRECISION');
        $this->addSql('CREATE UNIQUE INDEX idx_cointcontract_contractaddress_network ON coin_contract (contract_address, network)');
        $this->addSql('ALTER TABLE coin_detail ALTER liquidity TYPE NUMERIC(24, 10)');
        $this->addSql('ALTER TABLE coin_detail ALTER volume TYPE NUMERIC(22, 10)');
        $this->addSql('ALTER TABLE coin_detail ALTER total_supply TYPE NUMERIC(14, 0)');
        $this->addSql('ALTER TABLE coin_detail ALTER circulation_supply TYPE NUMERIC(24, 10)');
        $this->addSql('ALTER TABLE coin_detail ADD listed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE coin_detail DROP max_supply');
        $this->addSql('ALTER TABLE coin_detail ALTER market_cap TYPE NUMERIC(24, 10)');
    }
}
