<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use SchemaWeave\Adapter\CallbackDataProvider;
use SchemaWeave\Config;
use SchemaWeave\SchemaEngine;

// This example uses fictional table names and data only.
$pdo = new PDO('sqlite:' . __DIR__ . '/demo.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$provider = new CallbackDataProvider([
    'faqs' => static function (array $page) use ($pdo): array {
        if (empty($page['id'])) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT question, answer FROM demo_faq WHERE content_id = :content_id ORDER BY id ASC'
        );
        $statement->execute(['content_id' => (int) $page['id']]);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    },
    'related' => static function (array $page) use ($pdo): array {
        if (empty($page['id'])) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT c.title AS name, c.slug '
            . 'FROM demo_related r '
            . 'INNER JOIN demo_content c ON c.id = r.related_content_id '
            . 'WHERE r.content_id = :content_id ORDER BY r.id ASC'
        );
        $statement->execute(['content_id' => (int) $page['id']]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static function (array $row): array {
            return [
                'schema_type' => 'WebPage',
                'name' => (string) ($row['name'] ?? ''),
                'url' => 'https://example.com/' . ltrim((string) ($row['slug'] ?? ''), '/'),
            ];
        }, $rows);
    },
]);

$config = new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Industrial',
    'organization' => [
        'name' => 'Acme Industrial',
        'email' => 'hello@example.com',
    ],
]);

$engine = new SchemaEngine($config, $provider);

$graph = $engine->generate([
    'id' => 42,
    'type' => 'page',
    'name' => 'Example Service',
    'url' => 'https://example.com/example-service',
    'description' => 'Fictional PDO integration example.',
]);

echo $graph->toScriptTag();
