<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20260908131000 extends AbstractMigration
{
    private const int MAX_LENGTH = 80;
    private const int MIN_LENGTH = 2;

    public function getDescription(): string
    {
        return 'Append the creation year to existing post slugs';
    }

    public function up(Schema $schema): void
    {
    }

    public function postUp(Schema $schema): void
    {
        $slugger = new AsciiSlugger('fr');
        $used = [];
        $rows = $this->connection->fetchAllAssociative('SELECT id, title, created_at FROM post ORDER BY id ASC');
        $newSlugs = [];
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
            $newSlugs[(int) $row['id']] = $slug;
        }

        foreach ($newSlugs as $id => $slug) {
            $this->connection->update('post', ['slug' => '__tmp-'.$id], ['id' => $id]);
        }
        foreach ($newSlugs as $id => $slug) {
            $this->connection->update('post', ['slug' => $slug], ['id' => $id]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->write('Post slugs cannot be restored to the previous year-less values.');
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
