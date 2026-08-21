# Architecture

SchemaWeave separates structured-data generation from CMS-specific storage and routing.

```text
CMS / Application
   |
   +-- normalized page data
   +-- DataProviderInterface
   +-- UrlResolverInterface
   +-- optional SchemaExtensionInterface[]
   |
   v
SchemaEngine
   |
   +-- OrganizationSchema
   +-- WebsiteSchema
   +-- LocalBusinessSchema
   +-- WebPageSchema
   +-- ProductSchema
   +-- BlogPostingSchema
   +-- BreadcrumbSchema
   +-- ItemListSchema
   +-- FAQSchema
   |
   v
SchemaGraph -> JSON-LD
```

## Core responsibilities

The core knows Schema.org entity construction, graph references, data-integrity rules, JSON-LD rendering, and lightweight graph validation. It does not know WordPress tables, custom CMS tables, customer-specific IDs, or URL conventions.

## Adapter responsibilities

Adapters translate an application's data model into normalized arrays. Database/table knowledge belongs here, not in the core.

### `DataProviderInterface`

Provides breadcrumbs, collections, FAQ rows, related content, and additional product images.

### `UrlResolverInterface`

Resolves canonical page URLs when the normalized page object does not already provide one.

### `SchemaExtensionInterface`

Allows an integration to append additional entity types without modifying `SchemaEngine`. This is the preferred route for project-specific Service, Event, JobPosting, or other entities.

## WordPress bridge

The WordPress plugin is an adapter around the same core. It owns WordPress settings, post metadata, canonical permalinks, archives, author data, visible FAQ rendering, and optional WooCommerce product enrichment.

The core remains usable independently through Composer or the standalone PHP package.
