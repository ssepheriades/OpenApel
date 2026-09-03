<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\PageView;
use App\State\PageCatalogApiProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'Page',
    operations: [
        new GetCollection(
            uriTemplate: '/pages',
            normalizationContext: ['groups' => ['page:read'], 'skip_null_values' => false],
            provider: PageCatalogApiProvider::class,
            paginationEnabled: false,
        ),
        new Get(
            uriTemplate: '/pages/{slug}',
            uriVariables: ['slug'],
            normalizationContext: ['groups' => ['page:read'], 'skip_null_values' => false],
            provider: PageCatalogApiProvider::class,
        ),
    ],
)]
final readonly class PageResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        #[Groups(['page:read'])]
        public string $slug,
        #[Groups(['page:read'])]
        public string $kind,
        #[Groups(['page:read'])]
        public string $title,
        #[Groups(['page:read'])]
        public ?string $subtitle,
        #[Groups(['page:read'])]
        public ?string $body,
        #[Groups(['page:read'])]
        public bool $visible,
    ) {
    }

    public static function fromView(PageView $view): self
    {
        return new self(
            slug: $view->slug,
            kind: $view->kind,
            title: $view->title,
            subtitle: $view->subtitle,
            body: $view->body,
            visible: $view->visible,
        );
    }
}
