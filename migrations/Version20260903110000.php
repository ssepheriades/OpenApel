<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add visibility switch on catalogue pages (used by legal documents)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD visible BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE page ALTER COLUMN visible DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP visible');
    }
}
