<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260925103216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional cover image on catalogue pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD cover_image_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP cover_image_filename');
    }
}
