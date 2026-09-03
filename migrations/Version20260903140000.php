<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Neutralize leftover APEL branding in default team page title and theme name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE page SET title = 'Équipe' WHERE slug = 'team' AND title = 'Votre Équipe APEL'");
        $this->addSql("UPDATE content_theme SET name = 'Association' WHERE name = 'APEL' AND NOT EXISTS (SELECT 1 FROM content_theme ct2 WHERE ct2.name = 'Association')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE page SET title = 'Votre Équipe APEL' WHERE slug = 'team' AND title = 'Équipe'");
        $this->addSql("UPDATE content_theme SET name = 'APEL' WHERE name = 'Association' AND NOT EXISTS (SELECT 1 FROM content_theme ct2 WHERE ct2.name = 'APEL')");
    }
}
