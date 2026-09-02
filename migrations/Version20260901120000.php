<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename faq.state to visibility, add created_at, and index both columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE faq SET state = 'visible' WHERE state IS NULL OR state NOT IN ('visible', 'hidden')");
        $this->addSql('ALTER TABLE faq RENAME COLUMN state TO visibility');
        $this->addSql('ALTER TABLE faq ALTER COLUMN visibility TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE faq ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW() NOT NULL');
        $this->addSql('ALTER TABLE faq ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('CREATE INDEX idx_faq_created_at ON faq (created_at)');
        $this->addSql('CREATE INDEX idx_faq_visibility ON faq (visibility)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_faq_created_at');
        $this->addSql('DROP INDEX idx_faq_visibility');
        $this->addSql('ALTER TABLE faq DROP created_at');
        $this->addSql('ALTER TABLE faq ALTER COLUMN visibility TYPE VARCHAR(16)');
        $this->addSql('ALTER TABLE faq RENAME COLUMN visibility TO state');
    }
}
