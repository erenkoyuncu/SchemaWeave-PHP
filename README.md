<p align="center">
  <img src="branding/schemaweave-hero.jpg" alt="SchemaWeave — Structured Data Engine for WordPress & PHP" width="100%">
</p>

<h1 align="center">SchemaWeave PHP</h1>

<p align="center">
  <strong>Framework-agnostic Schema.org JSON-LD engine for PHP 7.4+.</strong><br>
  The independent core behind the SchemaWeave WordPress plugin.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/Schema.org-JSON--LD-2563EB?style=flat-square" alt="Schema.org JSON-LD">
  <img src="https://img.shields.io/badge/PSR--4-Autoloading-0EA5E9?style=flat-square" alt="PSR-4">
  <img src="https://img.shields.io/badge/License-MIT-16A34A?style=flat-square" alt="MIT License">
</p>

---

## What it is

SchemaWeave PHP is a small, dependency-light structured-data engine that can be embedded in custom PHP applications, CMS projects, PDO-based systems and framework adapters.

It is intentionally separate from WordPress. The WordPress integration lives in [SchemaWeave](https://github.com/erenkoyuncu/SchemaWeave), while this repository remains the **source of truth for the core engine**.

## Principles

- Generate structured data from real application data.
- Do not fabricate prices, offers, SKU/MPN identifiers, ratings or reviews.
- Keep the core independent from WordPress and other frameworks.
- Allow data providers, URL resolvers and schema extensions to be replaced.
- Build one coherent `@graph` with stable entity relationships.
- Omit incomplete entities instead of emitting broken structured data.

## Supported entities

- `Organization`
- `WebSite`
- `LocalBusiness`
- `WebPage`
- `AboutPage`
- `ContactPage`
- `ProfilePage`
- `SearchResultsPage`
- `CollectionPage`
- `Product`
- `BlogPosting`
- `BreadcrumbList`
- `ItemList`
- `FAQPage`
- custom entities through the extension API

## Requirements

- PHP 7.4 or newer

## Composer

The package metadata is ready for:

```bash
composer require erenkoyuncu/schemaweave
```

> Packagist publication can follow the public GitHub `v1.0.0` release. Until then, use the repository or a release archive directly.

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

## Architecture

SchemaWeave PHP is built around replaceable contracts rather than a specific CMS.

```text
Application / CMS
      │
      ├── DataProviderInterface
      ├── UrlResolverInterface
      └── Config
              │
              ▼
        SchemaEngine
              │
              ├── Organization
              ├── WebSite
              ├── WebPage
              ├── Product
              ├── BlogPosting
              ├── BreadcrumbList
              ├── ItemList
              └── FAQPage
              │
              ▼
         SchemaGraph
```

Ready-to-use implementations include array and callback data providers plus the default URL resolver.

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for more detail.

## Adapters

The core exposes:

- `DataProviderInterface`
- `UrlResolverInterface`
- `SchemaExtensionInterface`

Examples are available for:

- plain PHP,
- PDO-backed applications,
- custom data providers,
- custom schema extensions.

See the [`examples/`](examples/) directory and [PHP integration guide](docs/PHP-INTEGRATION.md).

## Data integrity

Missing commercial or reputation data is omitted instead of invented.

For example, a Product without a real price should not suddenly gain a made-up `Offer`, and content without real review data should not gain an `AggregateRating`.

See [docs/DATA-INTEGRITY.md](docs/DATA-INTEGRITY.md).

## Output safety

JSON-LD script output is encoded to prevent HTML/script breakout sequences while preserving valid JSON-LD. Date helpers normalize structured-data timestamps without silently applying an unrelated server timezone.

## WordPress ecosystem

The WordPress plugin bundles a release copy of this engine so WordPress.org users do not need Composer.

| Repository | Purpose |
| --- | --- |
| **SchemaWeave-PHP** | Core engine, contracts, graph generation and tests |
| **[SchemaWeave](https://github.com/erenkoyuncu/SchemaWeave)** | WordPress/WooCommerce adapter, admin UI, FAQ display, diagnostics and WP-CLI |

## Development

Run the core tests with:

```bash
php tests/run.php
```

The suite covers graph generation, extensions, data-integrity behavior, dates, validation and output escaping.

## Security

See [SECURITY.md](SECURITY.md).

## Contributing

Issues and pull requests are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

SchemaWeave PHP is released under the **MIT License**. See [LICENSE](LICENSE).
