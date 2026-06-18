<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611200833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix coin table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin ALTER price DROP NOT NULL');
        $this->addSql('ALTER TABLE coin ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE coin ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE coin ALTER network SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coin ALTER price SET NOT NULL');
        $this->addSql('ALTER TABLE coin DROP created_at');
        $this->addSql('ALTER TABLE coin DROP updated_at');
        $this->addSql('ALTER TABLE coin ALTER network DROP NOT NULL');
    }
}
