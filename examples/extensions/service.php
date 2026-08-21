<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use SchemaWeave\Adapter\CallbackSchemaExtension;
use SchemaWeave\Config;
use SchemaWeave\SchemaEngine;

$config = new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Industrial',
    'organization' => ['name' => 'Acme Industrial'],
]);

$serviceExtension = new CallbackSchemaExtension(
    static function (array $page, array $config): array {
        if (($page['custom_kind'] ?? '') !== 'service') {
            return [];
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');

        return [
            '@type' => 'Service',
            '@id' => (string) $page['url'] . '#service',
            'name' => (string) $page['name'],
            'url' => (string) $page['url'],
            'provider' => ['@id' => $baseUrl . '/#organization'],
            'areaServed' => 'US',
        ];
    }
);

$engine = new SchemaEngine($config, null, null, [$serviceExtension]);

echo $engine->generate([
    'type' => 'page',
    'custom_kind' => 'service',
    'name' => 'Example Maintenance Service',
    'url' => 'https://example.com/services/maintenance',
])->toScriptTag();
