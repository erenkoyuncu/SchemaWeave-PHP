<?php
namespace SchemaWeave\Adapter;

use SchemaWeave\Contracts\DataProviderInterface;

final class NullDataProvider implements DataProviderInterface
{
    public function getBreadcrumbItems(array $page): array { return []; }
    public function getCollectionItems(array $page): array { return []; }
    public function getFaqItems(array $page): array { return []; }
    public function getRelatedItems(array $page): array { return []; }
    public function getProductImages(array $page): array { return []; }
}
