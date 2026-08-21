<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Image;
use SchemaWeave\Support\Text;

final class WebPageSchema
{
    private const ALLOWED_TYPES = [
        'WebPage', 'AboutPage', 'ContactPage', 'CollectionPage', 'ProfilePage', 'SearchResultsPage'
    ];

    public static function build(array $page, array $config, string $url, ?string $breadcrumbId = null): ?array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $name = Text::clean($page['name'] ?? '', 200);
        $url = trim($url);
        if ($name === '' || $url === '') {
            return null;
        }

        $requestedType = (string) ($page['schema_page_type'] ?? 'WebPage');
        $pageType = in_array($requestedType, self::ALLOWED_TYPES, true)
            ? $requestedType
            : 'WebPage';

        if (($page['type'] ?? '') === 'collection' && $pageType !== 'SearchResultsPage') {
            $pageType = 'CollectionPage';
        }

        $entity = [
            '@type' => $pageType,
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $name,
            'inLanguage' => (string) ($page['language'] ?? ($config['default_language'] ?? 'en-US')),
        ];

        if (self::schemaEnabled($config, 'website')) {
            $entity['isPartOf'] = ['@id' => $baseUrl . '/#website'];
        }

        if (self::schemaEnabled($config, 'organization')) {
            $entity['publisher'] = ['@id' => $baseUrl . '/#organization'];
        }

        if (!empty($page['description'])) {
            $entity['description'] = Text::clean($page['description'], 500);
        }

        $image = Image::absolute($baseUrl, $page['image'] ?? '');
        if ($image) {
            $entity['image'] = $image;
        }

        if ($breadcrumbId) {
            $entity['breadcrumb'] = ['@id' => $breadcrumbId];
        }

        if (
            self::schemaEnabled($config, 'organization')
            && in_array($pageType, ['AboutPage', 'ContactPage'], true)
        ) {
            $entity['about'] = ['@id' => $baseUrl . '/#organization'];
        }

        if ($pageType === 'AboutPage' && self::schemaEnabled($config, 'organization')) {
            $entity['mainEntity'] = ['@id' => $baseUrl . '/#organization'];
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
