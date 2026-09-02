<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901131500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add editable homepage title and markdown intro on site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD home_title VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE site_settings ADD home_text TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP home_title');
        $this->addSql('ALTER TABLE site_settings DROP home_text');
    }
}
