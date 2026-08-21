<?php
namespace SchemaWeave\Schema;

use SchemaWeave\Support\Image;
use SchemaWeave\Support\Text;

final class OrganizationSchema
{
    public static function build(array $config): ?array
    {
        $org = isset($config['organization']) && is_array($config['organization'])
            ? $config['organization']
            : [];

        $name = Text::clean($org['name'] ?? '', 200);
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');

        if ($name === '' || $baseUrl === '') {
            return null;
        }

        $entity = [
            '@type' => $org['type'] ?? 'Organization',
            '@id' => $baseUrl . '/#organization',
            'name' => $name,
            'url' => $baseUrl . '/',
        ];

        foreach (['description', 'email', 'telephone'] as $field) {
            if (!empty($org[$field])) {
                $entity[$field] = $field === 'description'
                    ? Text::clean($org[$field], 500)
                    : (string) $org[$field];
            }
        }

        if (!empty($org['alternate_name'])) {
            $entity['alternateName'] = $org['alternate_name'];
        }

        $logo = Image::absolute($baseUrl, $org['logo'] ?? '');
        if ($logo) {
            $entity['logo'] = [
                '@type' => 'ImageObject',
                '@id' => $baseUrl . '/#logo',
                'url' => $logo,
                'contentUrl' => $logo,
            ];
        }

        if (!empty($org['same_as']) && is_array($org['same_as'])) {
            $entity['sameAs'] = array_values(array_unique(array_filter($org['same_as'])));
        }

        return $entity;
    }
}
