<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905185800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional cover image on posts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD cover_image_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP cover_image_filename');
    }
}
