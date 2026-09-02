<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260826154354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD photo_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE app_user SET roles = '[\"ROLE_ADMIN\"]' WHERE roles::text = '[\"ROLE_STAFF\"]'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE app_user SET roles = '[\"ROLE_STAFF\"]' WHERE roles::text = '[\"ROLE_ADMIN\"]'");
        $this->addSql('ALTER TABLE app_user DROP photo_filename');
    }
}
