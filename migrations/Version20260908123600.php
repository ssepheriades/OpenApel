<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20260908123600 extends AbstractMigration
{
    private const int MAX_LENGTH = 80;
    private const int MIN_LENGTH = 2;

    public function getDescription(): string
    {
        return 'Unique public slugs for posts and events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD slug VARCHAR(80) DEFAULT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $slugger = new AsciiSlugger('fr');
        $this->backfillPosts($slugger);
        $this->backfillEvents($slugger);

        $this->connection->executeStatement('ALTER TABLE post ALTER slug SET NOT NULL');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_post_slug ON post (slug)');
        $this->connection->executeStatement('ALTER TABLE event ALTER slug SET NOT NULL');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_event_slug ON event (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_post_slug');
        $this->addSql('ALTER TABLE post DROP slug');
        $this->addSql('DROP INDEX uniq_event_slug');
        $this->addSql('ALTER TABLE event DROP slug');
    }

    private function backfillPosts(AsciiSlugger $slugger): void
    {
        $used = [];
        $rows = $this->connection->fetchAllAssociative('SELECT id, title, created_at FROM post ORDER BY id ASC');
        foreach ($rows as $row) {
            $base = $this->slugify($slugger, (string) $row['title'], 'article');
            $createdAt = $row['created_at'] ?? null;
            if (\is_string($createdAt) && '' !== $createdAt) {
                $year = substr($createdAt, 0, 4);
                if (ctype_digit($year) && !\in_array($year, explode('-', $base), true)) {
                    $base = $this->truncate($base, self::MAX_LENGTH - (1 + strlen($year))).'-'.$year;
                }
            }
            $slug = $this->uniquify($base, $used);
            $used[$slug] = true;
            $this->connection->update('post', ['slug' => $slug], ['id' => $row['id']]);
        }
    }

    private function backfillEvents(AsciiSlugger $slugger): void
    {
        $used = [];
        $rows = $this->connection->fetchAllAssociative('SELECT id, title, starts_at FROM event ORDER BY id ASC');
        foreach ($rows as $row) {
            $base = $this->slugify($slugger, (string) $row['title'], 'evenement');
            $startsAt = $row['starts_at'] ?? null;
            if (\is_string($startsAt) && '' !== $startsAt) {
                $year = substr($startsAt, 0, 4);
                if (ctype_digit($year) && !\in_array($year, explode('-', $base), true)) {
                    $base = $this->truncate($base, self::MAX_LENGTH - (1 + strlen($year))).'-'.$year;
                }
            }
            $slug = $this->uniquify($base, $used);
            $used[$slug] = true;
            $this->connection->update('event', ['slug' => $slug], ['id' => $row['id']]);
        }
    }

    /**
     * @param array<string, true> $used
     */
    private function uniquify(string $base, array $used): string
    {
        if (!isset($used[$base])) {
            return $base;
        }

        for ($n = 2; $n <= 1000; ++$n) {
            $suffix = '-'.$n;
            $candidate = $this->truncate($base, self::MAX_LENGTH - strlen($suffix)).$suffix;
            if (!isset($used[$candidate])) {
                return $candidate;
            }
        }

        throw new \RuntimeException(sprintf('Unable to allocate a unique slug from "%s".', $base));
    }

    private function slugify(AsciiSlugger $slugger, string $title, string $fallback): string
    {
        $slug = strtolower($slugger->slug($title)->toString());
        if (strlen($slug) < self::MIN_LENGTH) {
            $slug = $fallback;
        }

        return $this->truncate($slug, self::MAX_LENGTH);
    }

    private function truncate(string $slug, int $max): string
    {
        if (strlen($slug) <= $max) {
            return $slug;
        }

        return rtrim(substr($slug, 0, $max), '-');
    }
}
