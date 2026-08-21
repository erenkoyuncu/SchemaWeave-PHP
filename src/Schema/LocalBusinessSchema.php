<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Text;

final class LocalBusinessSchema
{
    public static function buildAll(array $config): array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $locations = isset($config['locations']) && is_array($config['locations'])
            ? $config['locations']
            : [];
        $rows = [];

        foreach ($locations as $index => $location) {
            if (!is_array($location)) {
                continue;
            }

            $name = Text::clean($location['name'] ?? '', 200);
            if ($name === '') {
                continue;
            }

            $id = trim((string) ($location['id'] ?? ('location-' . ($index + 1))));
            $entity = [
                '@type' => $location['type'] ?? 'LocalBusiness',
                '@id' => $baseUrl . '/#' . $id,
                'name' => $name,
            ];

            if (self::schemaEnabled($config, 'organization')) {
                $entity['branchOf'] = ['@id' => $baseUrl . '/#organization'];
            }

            foreach (['url', 'telephone', 'email', 'faxNumber'] as $field) {
                if (!empty($location[$field])) {
                    $entity[$field] = (string) $location[$field];
                }
            }

            if (!empty($location['address']) && is_array($location['address'])) {
                $address = array_filter($location['address'], static function ($value) {
                    return trim((string) $value) !== '';
                });
                if (!empty($address)) {
                    $entity['address'] = array_merge(['@type' => 'PostalAddress'], $address);
                }
            }

            $rows[] = $entity;
        }

        return $rows;
    }

    private static function schemaEnabled(array $config, string $schema): bool
    {
        $schemas = isset($config['schemas']) && is_array($config['schemas'])
            ? $config['schemas']
            : [];

        return !array_key_exists($schema, $schemas) || !empty($schemas[$schema]);
    }
}
