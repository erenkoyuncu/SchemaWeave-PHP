<?php
namespace SchemaWeave\Adapter;

use SchemaWeave\Contracts\DataProviderInterface;

/**
 * Framework-neutral adapter backed by application callbacks.
 *
 * Each callback receives the current page array and must return an array.
 * Missing callbacks safely resolve to an empty array.
 */
final class CallbackDataProvider implements DataProviderInterface
{
    /** @var array<string, callable> */
    private array $callbacks;

    public function __construct(array $callbacks = [])
    {
        $this->callbacks = [];

        foreach ($callbacks as $key => $callback) {
            if (is_string($key) && is_callable($callback)) {
                $this->callbacks[$key] = $callback;
            }
        }
    }

    public function getBreadcrumbItems(array $page): array
    {
        return $this->invoke('breadcrumbs', $page);
    }

    public function getCollectionItems(array $page): array
    {
        return $this->invoke('collection', $page);
    }

    public function getFaqItems(array $page): array
    {
        return $this->invoke('faqs', $page);
    }

    public function getRelatedItems(array $page): array
    {
        return $this->invoke('related', $page);
    }

    public function getProductImages(array $page): array
    {
        return $this->invoke('product_images', $page);
    }

    private function invoke(string $key, array $page): array
    {
        if (!isset($this->callbacks[$key])) {
            return [];
        }

        $value = call_user_func($this->callbacks[$key], $page);

        return is_array($value) ? $value : [];
    }
}
