<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260902164500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact email sending switch on site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD contact_email_enabled BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN contact_email_enabled DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP contact_email_enabled');
    }
}
