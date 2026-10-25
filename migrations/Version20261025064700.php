<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025064700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_verification DROP CONSTRAINT fk_da3db909a76ed395');
        $this->addSql('DROP INDEX idx_da3db909a76ed395');
        $this->addSql('ALTER TABLE user_verification RENAME COLUMN user_id TO userId');
        $this->addSql('ALTER TABLE user_verification ADD CONSTRAINT FK_DA3DB90964B64DCC FOREIGN KEY (userId) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_DA3DB90964B64DCC ON user_verification (userId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_verification DROP CONSTRAINT FK_DA3DB90964B64DCC');
        $this->addSql('DROP INDEX IDX_DA3DB90964B64DCC');
        $this->addSql('ALTER TABLE user_verification RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE user_verification ADD CONSTRAINT fk_da3db909a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_da3db909a76ed395 ON user_verification (user_id)');
    }
}
