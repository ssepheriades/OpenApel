<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924121800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an optional quote on team member accounts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD quote VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user DROP quote');
    }
}
