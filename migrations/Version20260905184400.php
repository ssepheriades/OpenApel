<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905184400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add event.updated_at so Vich can persist hero/flyer uploads';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER updated_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP updated_at');
    }
}
