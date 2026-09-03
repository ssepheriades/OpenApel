<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Page;

/**
 * Cache-friendly snapshot of one catalogue page.
 */
final readonly class PageView
{
    public function __construct(
        public string $slug,
        public string $kind,
        public string $title,
        public ?string $subtitle,
        public ?string $body,
        public bool $visible,
    ) {
    }

    public static function fromEntity(Page $page): self
    {
        return new self(
            slug: $page->getSlug()->value,
            kind: $page->getKind()->value,
            title: $page->getTitle(),
            subtitle: $page->getSubtitle(),
            body: $page->getBody(),
            visible: $page->isVisible(),
        );
    }
}
