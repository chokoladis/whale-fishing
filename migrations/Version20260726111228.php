<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726111228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_9bace7e1c74f2195');
        $this->addSql('ALTER TABLE refresh_tokens ALTER token_hash TYPE VARCHAR(64)');
        $this->addSql('ALTER TABLE refresh_tokens ALTER is_revoked DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_9bace7e19d86650f RENAME TO IDX_9BACE7E1A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_restore ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE password_restore DROP "user"');
        $this->addSql('ALTER TABLE refresh_tokens ALTER token_hash TYPE CHAR(64)');
        $this->addSql('ALTER TABLE refresh_tokens ALTER is_revoked SET DEFAULT false');
        $this->addSql('CREATE UNIQUE INDEX uniq_9bace7e1c74f2195 ON refresh_tokens (token_hash)');
        $this->addSql('ALTER INDEX idx_9bace7e1a76ed395 RENAME TO idx_9bace7e19d86650f');
    }
}
