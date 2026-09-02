<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260902110200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace school_class.grade string with FK to grade, and index grade.weight';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_grade_weight ON grade (weight)');
        $this->addSql('ALTER TABLE school_class DROP COLUMN grade');
        $this->addSql('ALTER TABLE school_class ADD grade_id INT NOT NULL');
        $this->addSql('ALTER TABLE school_class ADD CONSTRAINT fk_school_class_grade FOREIGN KEY (grade_id) REFERENCES grade (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX idx_school_class_grade ON school_class (grade_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE school_class DROP CONSTRAINT fk_school_class_grade');
        $this->addSql('DROP INDEX idx_school_class_grade');
        $this->addSql('ALTER TABLE school_class DROP COLUMN grade_id');
        $this->addSql('ALTER TABLE school_class ADD grade VARCHAR(16) NOT NULL');
        $this->addSql('DROP INDEX idx_grade_weight');
    }
}
