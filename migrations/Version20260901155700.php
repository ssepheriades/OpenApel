<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901155700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add faq.theme with default autre';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE faq ADD theme VARCHAR(32) DEFAULT 'autre' NOT NULL");
        $this->addSql('ALTER TABLE faq ALTER COLUMN theme DROP DEFAULT');
        $this->addSql('CREATE INDEX idx_faq_theme ON faq (theme)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_faq_theme');
        $this->addSql('ALTER TABLE faq DROP theme');
    }
}
