<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Text;

final class BreadcrumbSchema
{
    public static function build(array $items, string $currentUrl): ?array
    {
        $elements = [];
        $position = 1;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = Text::clean($item['name'] ?? '', 200);
            $url = trim((string) ($item['url'] ?? ''));
            if ($name === '' || $url === '') {
                continue;
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url,
            ];
        }

        if (empty($elements)) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $currentUrl . '#breadcrumb',
            'itemListElement' => $elements,
        ];
    }
}
