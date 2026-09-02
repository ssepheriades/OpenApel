<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260902112900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add public page visibility flags on site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD faq_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD team_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD posts_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD agenda_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN faq_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN team_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN posts_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN agenda_visible DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP faq_visible');
        $this->addSql('ALTER TABLE site_settings DROP team_visible');
        $this->addSql('ALTER TABLE site_settings DROP posts_visible');
        $this->addSql('ALTER TABLE site_settings DROP agenda_visible');
    }
}
