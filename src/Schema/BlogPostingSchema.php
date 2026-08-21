<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Date;
use SchemaWeave\Support\Image;
use SchemaWeave\Support\Text;

final class BlogPostingSchema
{
    public static function build(array $page, array $config, string $url): ?array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $name = Text::clean($page['name'] ?? '', 200);
        $url = trim($url);
        if ($name === '' || $url === '') {
            return null;
        }

        $entity = [
            '@type' => 'BlogPosting',
            '@id' => $url . '#article',
            'url' => $url,
            'headline' => $name,
            'name' => $name,
            'inLanguage' => (string) ($page['language'] ?? ($config['default_language'] ?? 'en-US')),
        ];

        if (self::schemaEnabled($config, 'webpage')) {
            $entity['mainEntityOfPage'] = ['@id' => $url . '#webpage'];
        }

        if (self::schemaEnabled($config, 'organization')) {
            $entity['publisher'] = ['@id' => $baseUrl . '/#organization'];
        }

        if (!empty($page['description'])) {
            $entity['description'] = Text::clean($page['description'], 500);
        }

        $image = Image::absolute($baseUrl, $page['image'] ?? '');
        if ($image) {
            $entity['image'] = ['@type' => 'ImageObject', 'url' => $image];
        }

        $published = Date::normalize($page['date_published'] ?? '');
        $modified = Date::normalize($page['date_modified'] ?? '');
        if ($published !== '') {
            $entity['datePublished'] = $published;
        }
        if ($modified !== '') {
            $entity['dateModified'] = $modified;
        }

        if (!empty($page['author']) && is_array($page['author'])) {
            $entity['author'] = $page['author'];
        } elseif (self::schemaEnabled($config, 'organization')) {
            $entity['author'] = ['@id' => $baseUrl . '/#organization'];
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
