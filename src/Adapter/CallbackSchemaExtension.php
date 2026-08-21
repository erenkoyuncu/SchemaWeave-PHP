<?php
namespace SchemaWeave\Adapter;

use SchemaWeave\Contracts\SchemaExtensionInterface;

final class CallbackSchemaExtension implements SchemaExtensionInterface
{
    /** @var callable */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function build(array $page, array $config): array
    {
        $entities = call_user_func($this->callback, $page, $config);
        if (!is_array($entities)) {
            return [];
        }

        if (isset($entities['@type'])) {
            return [$entities];
        }

        return array_values(array_filter($entities, 'is_array'));
    }
}
