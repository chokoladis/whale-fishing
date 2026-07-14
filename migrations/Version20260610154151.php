<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610154151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change table transaction';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD block_number VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD hash VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD "from" VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD "to" VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD amount NUMERIC(36, 18) NOT NULL');
        $this->addSql('ALTER TABLE transaction DROP price');
        $this->addSql('ALTER TABLE transaction DROP qty');
        $this->addSql('ALTER TABLE transaction DROP gas');
        $this->addSql('ALTER TABLE transaction DROP fee');
        $this->addSql('ALTER TABLE transaction DROP tx_hash');
        $this->addSql('ALTER TABLE transaction DROP note');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin DROP contract_address');
        $this->addSql('ALTER TABLE coin DROP network');
        $this->addSql('ALTER TABLE transaction ADD price NUMERIC(28, 8) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD qty NUMERIC(28, 8) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD gas NUMERIC(28, 8) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD fee NUMERIC(28, 8) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD tx_hash VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction DROP block_number');
        $this->addSql('ALTER TABLE transaction DROP hash');
        $this->addSql('ALTER TABLE transaction DROP "from"');
        $this->addSql('ALTER TABLE transaction DROP "to"');
        $this->addSql('ALTER TABLE transaction DROP amount');
    }
}
