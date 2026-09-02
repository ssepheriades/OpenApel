<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901160500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring school year start and end dates on site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE site_settings ADD school_year_start DATE DEFAULT '2000-08-01' NOT NULL");
        $this->addSql("ALTER TABLE site_settings ADD school_year_end DATE DEFAULT '2000-07-31' NOT NULL");
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN school_year_start DROP DEFAULT');
        $this->addSql('ALTER TABLE site_settings ALTER COLUMN school_year_end DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP school_year_start');
        $this->addSql('ALTER TABLE site_settings DROP school_year_end');
    }
}
