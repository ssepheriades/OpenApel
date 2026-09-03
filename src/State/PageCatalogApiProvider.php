<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\PageResource;
use App\Service\PageCatalogProvider;

/**
 * @implements ProviderInterface<PageResource>
 */
final class PageCatalogApiProvider implements ProviderInterface
{
    public function __construct(
        private readonly PageCatalogProvider $catalog,
    ) {
    }

    /**
     * @return list<PageResource>|PageResource|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PageResource|null
    {
        if ($operation instanceof GetCollection) {
            $resources = [];
            foreach ($this->catalog->all() as $view) {
                $resources[] = PageResource::fromView($view);
            }

            return $resources;
        }

        $slug = $uriVariables['slug'] ?? null;
        if (!\is_string($slug) || '' === $slug) {
            return null;
        }

        $view = $this->catalog->get($slug);

        return null === $view ? null : PageResource::fromView($view);
    }
}
