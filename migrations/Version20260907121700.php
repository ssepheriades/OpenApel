<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260907121700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unique filename on downloadable documents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX uniq_document_filename ON document (filename)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_document_filename');
    }
}
