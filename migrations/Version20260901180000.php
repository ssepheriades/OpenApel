<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add post.theme, index post state, and widen post.state for enum storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE post ADD theme VARCHAR(32) DEFAULT 'autre' NOT NULL");
        $this->addSql('ALTER TABLE post ALTER COLUMN theme DROP DEFAULT');
        $this->addSql('CREATE INDEX idx_post_theme ON post (theme)');
        $this->addSql('ALTER TABLE post ALTER COLUMN state TYPE VARCHAR(32)');
        $this->addSql('CREATE INDEX idx_post_state ON post (state)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_post_state');
        $this->addSql('DROP INDEX idx_post_theme');
        $this->addSql('ALTER TABLE post DROP theme');
        $this->addSql('ALTER TABLE post ALTER COLUMN state TYPE VARCHAR(16)');
    }
}
