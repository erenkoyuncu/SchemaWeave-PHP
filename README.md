# SchemaWeave PHP

Framework-agnostic **Schema.org JSON-LD engine for PHP 7.4+**.

SchemaWeave PHP is the core library behind the [SchemaWeave WordPress plugin](https://github.com/erenkoyuncu/SchemaWeave). It can also be used directly in custom PHP applications, CMS projects, PDO-based systems, or adapters of your own.

## Principles

- Generate structured data from real application data.
- Do not fabricate prices, offers, SKU/MPN identifiers, ratings, or reviews.
- Keep the core independent from WordPress and other frameworks.
- Allow data providers, URL resolvers, and schema extensions to be replaced.
- Produce a single `@graph` document with stable entity relationships.

## Supported entities

- Organization
- WebSite
- LocalBusiness
- WebPage, AboutPage, ContactPage, ProfilePage, SearchResultsPage, CollectionPage
- Product
- BlogPosting
- BreadcrumbList
- ItemList
- FAQPage
- Custom entities through the extension API

## Requirements

- PHP 7.4 or newer

## Composer

```bash
composer require erenkoyuncu/schemaweave
```

> Packagist publication may follow the GitHub v1.0.0 release. Until then, clone this repository or use a release archive.

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use SchemaWeave\Config;
use SchemaWeave\SchemaEngine;

$engine = new SchemaEngine(new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Demo',
    'organization' => [
        'name' => 'Acme Demo',
    ],
]));

$graph = $engine->generate([
    'type' => 'page',
    'name' => 'About Us',
    'url' => 'https://example.com/about',
]);

echo $graph->toScriptTag();
```

## Adapters

The core exposes `DataProviderInterface` and `UrlResolverInterface`. Ready-to-use array and callback providers are included. See [`examples/`](examples/) and [`docs/PHP-INTEGRATION.md`](docs/PHP-INTEGRATION.md).

## Data integrity

Missing commercial or reputation data is omitted rather than invented. See [`docs/DATA-INTEGRITY.md`](docs/DATA-INTEGRITY.md).

## Development

```bash
php tests/run.php
```

The test suite covers core graph generation, extension handling, data-integrity rules, date normalization, graph validation, and output escaping.

## Related project

For WordPress and WooCommerce integration, use [SchemaWeave](https://github.com/erenkoyuncu/SchemaWeave).

## Security

See [SECURITY.md](SECURITY.md).

## License

MIT License. See [LICENSE](LICENSE).
