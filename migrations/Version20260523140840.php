<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523140840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Фикс coin_link';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_link DROP CONSTRAINT fk_b57623384bbda7');
        $this->addSql('ALTER TABLE coin_link ADD type VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE coin_link ADD url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE coin_link DROP name');
        $this->addSql('ALTER TABLE coin_link ADD CONSTRAINT FK_B57623384BBDA7 FOREIGN KEY (coin_id) REFERENCES coin (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_coin_link_unique_coin_type ON coin_link UNIQUE (coin_id, type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_link DROP CONSTRAINT FK_B57623384BBDA7');
        $this->addSql('ALTER TABLE coin_link ADD name VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE coin_link DROP type');
        $this->addSql('ALTER TABLE coin_link DROP url');
        $this->addSql('ALTER TABLE coin_link ADD CONSTRAINT fk_b57623384bbda7 FOREIGN KEY (coin_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
