# Changelog

## 1.0.1 - 2026-08-21

- Prefer WordPress `wp_strip_all_tags()` when the core is bundled inside WordPress.
- Preserve the native PHP fallback for framework-agnostic and standalone usage.
- Added a justified PHPCS directive for the standalone fallback so WordPress Plugin Check can distinguish the compatibility path.

## 1.0.0 - 2026-08-20

- First stable release of the framework-agnostic PHP core.
- Added graph generation for Organization, WebSite, LocalBusiness, WebPage variants, Product, BlogPosting, BreadcrumbList, ItemList and FAQPage.
- Added array/callback data providers and URL resolver contracts.
- Added schema extension API.
- Added graph validation and safe JSON-LD script output.
- Added data-integrity rules that omit unavailable commercial, identifier, rating and review data.
- Added PHP 7.4+ compatibility and automated core tests.
