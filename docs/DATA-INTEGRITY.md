# Data integrity policy

SchemaWeave treats structured data as a representation of real page/business data, not a place to manufacture SEO fields.

## Never fabricated by the engine

SchemaWeave does not invent:

- prices
- stock values
- Offer/AggregateOffer data
- SKU/MPN/GTIN identifiers
- ratings
- reviews
- product brands

Those fields are emitted only when supplied by the integration or a trusted source such as WooCommerce.

## FAQ visibility

The WordPress integration keeps FAQ structured data aligned with visitor-visible content. Schema-only hidden FAQ content is not the default behavior.

## Customer isolation

Customer-specific database tables, private URLs, real addresses, credentials, or business rules must live in private adapters. Repository tests scan source files for known legacy customer-specific markers to reduce accidental leakage.

## Validation

`GraphValidator` performs lightweight package-level integrity checks such as duplicate `@id`, required core fields, invalid URLs, and malformed Product/FAQ structures. It does not replace Schema.org validators or search-engine rich-result testing tools.
