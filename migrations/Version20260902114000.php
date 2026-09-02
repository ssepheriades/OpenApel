<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260902114000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add grade/school_class join tables for faq, post and event audience targeting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE faq_grade (faq_id INT NOT NULL, grade_id INT NOT NULL, PRIMARY KEY (faq_id, grade_id))');
        $this->addSql('CREATE INDEX IDX_FD8E743181BEC8C2 ON faq_grade (faq_id)');
        $this->addSql('CREATE INDEX IDX_FD8E7431FE19A1A8 ON faq_grade (grade_id)');
        $this->addSql('ALTER TABLE faq_grade ADD CONSTRAINT fk_faq_grade_faq FOREIGN KEY (faq_id) REFERENCES faq (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE faq_grade ADD CONSTRAINT fk_faq_grade_grade FOREIGN KEY (grade_id) REFERENCES grade (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE faq_school_class (faq_id INT NOT NULL, school_class_id INT NOT NULL, PRIMARY KEY (faq_id, school_class_id))');
        $this->addSql('CREATE INDEX IDX_6C50E8A081BEC8C2 ON faq_school_class (faq_id)');
        $this->addSql('CREATE INDEX IDX_6C50E8A014463F54 ON faq_school_class (school_class_id)');
        $this->addSql('ALTER TABLE faq_school_class ADD CONSTRAINT fk_faq_school_class_faq FOREIGN KEY (faq_id) REFERENCES faq (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE faq_school_class ADD CONSTRAINT fk_faq_school_class_class FOREIGN KEY (school_class_id) REFERENCES school_class (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE post_grade (post_id INT NOT NULL, grade_id INT NOT NULL, PRIMARY KEY (post_id, grade_id))');
        $this->addSql('CREATE INDEX IDX_CE4122DB4B89032C ON post_grade (post_id)');
        $this->addSql('CREATE INDEX IDX_CE4122DBFE19A1A8 ON post_grade (grade_id)');
        $this->addSql('ALTER TABLE post_grade ADD CONSTRAINT fk_post_grade_post FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE post_grade ADD CONSTRAINT fk_post_grade_grade FOREIGN KEY (grade_id) REFERENCES grade (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE post_school_class (post_id INT NOT NULL, school_class_id INT NOT NULL, PRIMARY KEY (post_id, school_class_id))');
        $this->addSql('CREATE INDEX IDX_988CD2564B89032C ON post_school_class (post_id)');
        $this->addSql('CREATE INDEX IDX_988CD25614463F54 ON post_school_class (school_class_id)');
        $this->addSql('ALTER TABLE post_school_class ADD CONSTRAINT fk_post_school_class_post FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE post_school_class ADD CONSTRAINT fk_post_school_class_class FOREIGN KEY (school_class_id) REFERENCES school_class (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE event_grade (event_id INT NOT NULL, grade_id INT NOT NULL, PRIMARY KEY (event_id, grade_id))');
        $this->addSql('CREATE INDEX IDX_18411F1871F7E88B ON event_grade (event_id)');
        $this->addSql('CREATE INDEX IDX_18411F18FE19A1A8 ON event_grade (grade_id)');
        $this->addSql('ALTER TABLE event_grade ADD CONSTRAINT fk_event_grade_event FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE event_grade ADD CONSTRAINT fk_event_grade_grade FOREIGN KEY (grade_id) REFERENCES grade (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE event_school_class (event_id INT NOT NULL, school_class_id INT NOT NULL, PRIMARY KEY (event_id, school_class_id))');
        $this->addSql('CREATE INDEX IDX_DEA2A57671F7E88B ON event_school_class (event_id)');
        $this->addSql('CREATE INDEX IDX_DEA2A57614463F54 ON event_school_class (school_class_id)');
        $this->addSql('ALTER TABLE event_school_class ADD CONSTRAINT fk_event_school_class_event FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE event_school_class ADD CONSTRAINT fk_event_school_class_class FOREIGN KEY (school_class_id) REFERENCES school_class (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE faq_grade DROP CONSTRAINT fk_faq_grade_faq');
        $this->addSql('ALTER TABLE faq_grade DROP CONSTRAINT fk_faq_grade_grade');
        $this->addSql('DROP TABLE faq_grade');

        $this->addSql('ALTER TABLE faq_school_class DROP CONSTRAINT fk_faq_school_class_faq');
        $this->addSql('ALTER TABLE faq_school_class DROP CONSTRAINT fk_faq_school_class_class');
        $this->addSql('DROP TABLE faq_school_class');

        $this->addSql('ALTER TABLE post_grade DROP CONSTRAINT fk_post_grade_post');
        $this->addSql('ALTER TABLE post_grade DROP CONSTRAINT fk_post_grade_grade');
        $this->addSql('DROP TABLE post_grade');

        $this->addSql('ALTER TABLE post_school_class DROP CONSTRAINT fk_post_school_class_post');
        $this->addSql('ALTER TABLE post_school_class DROP CONSTRAINT fk_post_school_class_class');
        $this->addSql('DROP TABLE post_school_class');

        $this->addSql('ALTER TABLE event_grade DROP CONSTRAINT fk_event_grade_event');
        $this->addSql('ALTER TABLE event_grade DROP CONSTRAINT fk_event_grade_grade');
        $this->addSql('DROP TABLE event_grade');

        $this->addSql('ALTER TABLE event_school_class DROP CONSTRAINT fk_event_school_class_event');
        $this->addSql('ALTER TABLE event_school_class DROP CONSTRAINT fk_event_school_class_class');
        $this->addSql('DROP TABLE event_school_class');
    }
}
