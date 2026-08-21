<?php
namespace Example;

use SchemaWeave\Contracts\DataProviderInterface;

final class ExampleDataProvider implements DataProviderInterface
{
    public function getBreadcrumbItems(array $page): array
    {
        return [
            ['name' => 'Home', 'url' => 'https://example.com/'],
            ['name' => $page['name'] ?? 'Page', 'url' => $page['url'] ?? 'https://example.com/'],
        ];
    }

    public function getCollectionItems(array $page): array { return []; }
    public function getFaqItems(array $page): array { return []; }
    public function getRelatedItems(array $page): array { return []; }
    public function getProductImages(array $page): array { return []; }
}
