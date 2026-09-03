<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move public section visibility from site_settings onto page.visible';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE page SET visible = s.faq_visible FROM site_settings s WHERE page.slug = 'faq'");
        $this->addSql("UPDATE page SET visible = s.team_visible FROM site_settings s WHERE page.slug = 'team'");
        $this->addSql("UPDATE page SET visible = s.posts_visible FROM site_settings s WHERE page.slug = 'news'");
        $this->addSql("UPDATE page SET visible = s.agenda_visible FROM site_settings s WHERE page.slug = 'agenda'");
        $this->addSql('ALTER TABLE site_settings DROP faq_visible');
        $this->addSql('ALTER TABLE site_settings DROP team_visible');
        $this->addSql('ALTER TABLE site_settings DROP posts_visible');
        $this->addSql('ALTER TABLE site_settings DROP agenda_visible');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD faq_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD team_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD posts_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ADD agenda_visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN faq_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN team_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN posts_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN agenda_visible DROP DEFAULT');
        $this->addSql("UPDATE site_settings SET faq_visible = p.visible FROM page p WHERE p.slug = 'faq'");
        $this->addSql("UPDATE site_settings SET team_visible = p.visible FROM page p WHERE p.slug = 'team'");
        $this->addSql("UPDATE site_settings SET posts_visible = p.visible FROM page p WHERE p.slug = 'news'");
        $this->addSql("UPDATE site_settings SET agenda_visible = p.visible FROM page p WHERE p.slug = 'agenda'");
    }
}
