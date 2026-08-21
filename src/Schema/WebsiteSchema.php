<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Text;

final class WebsiteSchema
{
    public static function build(array $config): ?array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $org = isset($config['organization']) && is_array($config['organization'])
            ? $config['organization']
            : [];
        $name = Text::clean($config['site_name'] ?? ($org['name'] ?? ''), 200);

        if ($baseUrl === '' || $name === '') {
            return null;
        }

        $entity = [
            '@type' => 'WebSite',
            '@id' => $baseUrl . '/#website',
            'url' => $baseUrl . '/',
            'name' => $name,
            'inLanguage' => (string) ($config['default_language'] ?? 'en-US'),
        ];

        if (self::schemaEnabled($config, 'organization')) {
            $entity['publisher'] = ['@id' => $baseUrl . '/#organization'];
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
