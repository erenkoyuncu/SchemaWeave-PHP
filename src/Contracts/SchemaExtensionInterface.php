<?php
namespace SchemaWeave\Contracts;

interface SchemaExtensionInterface
{
    /**
     * Return zero or more Schema.org graph entities for the current page.
     *
     * Extensions own their custom data dependencies. The core only appends
     * returned entity arrays to the generated @graph.
     */
    public function build(array $page, array $config): array;
}
