<?php
namespace SchemaWeave\Adapter;

use SchemaWeave\Contracts\DataProviderInterface;

final class ArrayDataProvider implements DataProviderInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getBreadcrumbItems(array $page): array { return $this->items('breadcrumbs'); }
    public function getCollectionItems(array $page): array { return $this->items('collection'); }
    public function getFaqItems(array $page): array { return $this->items('faqs'); }
    public function getRelatedItems(array $page): array { return $this->items('related'); }
    public function getProductImages(array $page): array { return $this->items('product_images'); }

    private function items(string $key): array
    {
        return isset($this->data[$key]) && is_array($this->data[$key])
            ? $this->data[$key]
            : [];
    }
}
