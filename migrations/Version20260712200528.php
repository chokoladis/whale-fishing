<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260712200528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_contract DROP CONSTRAINT fk_237ae68284bbda7');
        $this->addSql('ALTER TABLE coin_contract DROP CONSTRAINT fk_237ae68263b134e2');
        $this->addSql('DROP INDEX idx_237ae68263b134e2');
        $this->addSql('ALTER TABLE coin_contract DROP coin_parent_id');
        $this->addSql('ALTER TABLE coin_contract ALTER coin_id SET NOT NULL');
        $this->addSql('ALTER TABLE coin_contract ADD CONSTRAINT FK_237AE68284BBDA7 FOREIGN KEY (coin_id) REFERENCES coin (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin_contract DROP CONSTRAINT FK_237AE68284BBDA7');
        $this->addSql('ALTER TABLE coin_contract ADD coin_parent_id INT NOT NULL');
        $this->addSql('ALTER TABLE coin_contract ALTER coin_id DROP NOT NULL');
        $this->addSql('ALTER TABLE coin_contract ADD CONSTRAINT fk_237ae68284bbda7 FOREIGN KEY (coin_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE coin_contract ADD CONSTRAINT fk_237ae68263b134e2 FOREIGN KEY (coin_parent_id) REFERENCES coin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_237ae68263b134e2 ON coin_contract (coin_parent_id)');
    }
}
