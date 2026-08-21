<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Text;

final class ItemListSchema
{
    public static function build(array $rows): array
    {
        $items = [];
        $position = 1;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = Text::clean($row['name'] ?? '', 200);
            $url = trim((string) ($row['url'] ?? ''));
            if ($name === '' || $url === '') {
                continue;
            }
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'url' => $url,
            ];
        }

        return [
            '@type' => 'ItemList',
            'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
            'numberOfItems' => count($items),
            'itemListElement' => $items,
        ];
    }
}
