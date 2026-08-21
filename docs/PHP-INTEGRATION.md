# PHP integration

## Standalone package

Download `schemaweave-php-<version>.zip`, extract it, and load the included autoloader:

```php
require __DIR__ . '/schemaweave-php/autoload.php';

use SchemaWeave\Config;
use SchemaWeave\SchemaEngine;

$engine = new SchemaEngine(new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Demo',
    'organization' => [
        'name' => 'Acme Demo',
        'email' => 'hello@example.com',
    ],
]));

$graph = $engine->generate([
    'type' => 'page',
    'name' => 'Example Page',
    'url' => 'https://example.com/example',
    'description' => 'Fictional example content.',
]);

echo $graph->toScriptTag();
```

## Normalized page types

The core recognizes these page `type` values directly:

- `page`
- `blog_post`
- `product`
- `collection`

A page can also supply `schema_page_type` such as `AboutPage`, `ContactPage`, `ProfilePage`, or `SearchResultsPage` where appropriate.

## Custom data provider

Implement `SchemaWeave\Contracts\DataProviderInterface` when breadcrumbs, collections, FAQ rows, related pages, or product images come from a database/CMS.

For small integrations, `SchemaWeave\Adapter\CallbackDataProvider` accepts callbacks instead of a concrete adapter class.

## PDO

`examples/pdo/` demonstrates a fictional database integration. The example table names and data are deliberately generic and are not copied from a customer project.

## Custom entity extension

```php
use SchemaWeave\Adapter\CallbackSchemaExtension;

$extension = new CallbackSchemaExtension(static function (array $page): array {
    return [
        '@type' => 'Service',
        '@id' => $page['url'] . '#service',
        'name' => 'Example Service',
        'url' => $page['url'],
    ];
});
```

Pass extensions as the fourth `SchemaEngine` constructor argument.
