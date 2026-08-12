<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260812154227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_coin ALTER balance TYPE NUMERIC(44, 18)');
        $this->addSql('ALTER TABLE transaction ALTER amount TYPE NUMERIC(45, 25)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ALTER amount TYPE NUMERIC(36, 18)');
        $this->addSql('ALTER TABLE wallet_coin ALTER balance TYPE NUMERIC(36, 18)');
    }
}
