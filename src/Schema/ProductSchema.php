<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Image;
use SchemaWeave\Support\Text;

final class ProductSchema
{
    public static function build(array $page, array $config, string $url, array $providerImages = []): ?array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $name = Text::clean($page['name'] ?? '', 200);
        $url = trim($url);
        if ($name === '' || $url === '') {
            return null;
        }

        $entity = [
            '@type' => 'Product',
            '@id' => $url . '#product',
            'name' => $name,
            'url' => $url,
        ];

        if (self::schemaEnabled($config, 'webpage')) {
            $entity['mainEntityOfPage'] = ['@id' => $url . '#webpage'];
        }

        if (!empty($page['description'])) {
            $entity['description'] = Text::clean($page['description'], 500);
        }

        $images = [];
        if (!empty($page['image'])) {
            $image = Image::absolute($baseUrl, $page['image']);
            if ($image) { $images[] = $image; }
        }
        foreach ($providerImages as $imageValue) {
            $image = Image::absolute($baseUrl, $imageValue);
            if ($image) { $images[] = $image; }
        }
        $images = array_values(array_unique($images));
        if (!empty($images)) {
            $entity['image'] = $images;
        }

        if (!empty($page['brand'])) {
            $entity['brand'] = ['@type' => 'Brand', 'name' => Text::clean($page['brand'], 200)];
        }

        foreach (['sku', 'mpn', 'gtin', 'gtin8', 'gtin12', 'gtin13', 'gtin14'] as $field) {
            if (!empty($page[$field])) {
                $entity[$field] = (string) $page[$field];
            }
        }

        // Integrity rule: never invent commercial/review data. Add only explicitly supplied verified values.
        if (!empty($page['offers']) && is_array($page['offers'])) {
            $entity['offers'] = $page['offers'];
        }
        if (!empty($page['aggregate_rating']) && is_array($page['aggregate_rating'])) {
            $entity['aggregateRating'] = $page['aggregate_rating'];
        }
        if (!empty($page['reviews']) && is_array($page['reviews'])) {
            $entity['review'] = $page['reviews'];
        }

        return $entity;
    }

    private static function schemaEnabled(array $config, string $schema): bool
    {
        $schemas = isset($config['schemas']) && is_array($config['schemas'])
            ? $config['schemas']
            : [];

        return !array_key_exists($schema, $schemas) || !empty($schemas[$schema]);
    }
}

