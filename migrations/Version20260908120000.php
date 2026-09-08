<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make event.is_all_day required, default false';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE event SET is_all_day = false WHERE is_all_day IS NULL');
        $this->addSql('ALTER TABLE event ALTER is_all_day SET DEFAULT false');
        $this->addSql('ALTER TABLE event ALTER is_all_day SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER is_all_day DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER is_all_day DROP DEFAULT');
    }
}
