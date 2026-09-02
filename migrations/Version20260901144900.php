<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901144900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add weight column on app_user to sort team members by importance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD weight INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_app_user_weight ON app_user (weight)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_app_user_weight');
        $this->addSql('ALTER TABLE app_user DROP weight');
    }
}
