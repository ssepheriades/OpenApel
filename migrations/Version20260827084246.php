<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260827084246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD short_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD type VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE event ADD state VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP short_description');
        $this->addSql('ALTER TABLE event DROP type');
        $this->addSql('ALTER TABLE event DROP state');
    }
}
