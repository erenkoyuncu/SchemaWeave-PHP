<?php
require_once __DIR__ . '/bootstrap.php';

use SchemaWeave\Adapter\ArrayDataProvider;
use SchemaWeave\Adapter\CallbackDataProvider;
use SchemaWeave\Adapter\CallbackSchemaExtension;
use SchemaWeave\Config;
use SchemaWeave\GraphValidator;
use SchemaWeave\SchemaEngine;
use SchemaWeave\Support\Date;

$config = new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Demo',
    'organization' => [
        'name' => 'Acme Demo',
        'email' => 'hello@example.com',
        'same_as' => ['https://www.linkedin.com/company/example/'],
    ],
    'locations' => [[
        'id' => 'main-office',
        'name' => 'Acme Main Office',
        'address' => [
            'addressLocality' => 'Example City',
            'addressCountry' => 'US',
        ],
    ]],
]);

$provider = new ArrayDataProvider([
    'faqs' => [['question' => 'Is this fictional?', 'answer' => 'Yes.']],
    'product_images' => ['https://example.com/product-2.jpg'],
]);

$engine = new SchemaEngine($config, $provider);
$document = $engine->generate([
    'type' => 'product',
    'name' => 'Example Machine',
    'url' => 'https://example.com/products/example-machine',
    'description' => 'Fictional product for automated tests.',
    'image' => 'https://example.com/product.jpg',
    'include_locations' => true,
])->toArray();

expectTrue(($document['@context'] ?? '') === 'https://schema.org', 'Schema context should be present.');
expectTrue(!empty($document['@graph']), 'Graph should not be empty.');

$product = findEntity($document, 'Product');
expectTrue(is_array($product), 'Product entity should exist.');
expectTrue(!isset($product['offers']), 'Offers must not be fabricated.');
expectTrue(!isset($product['aggregateRating']), 'Ratings must not be fabricated.');
expectTrue(!isset($product['sku']), 'SKU must not be fabricated.');

$incompleteDocument = $engine->generate([
    'type' => 'product',
    'name' => '',
    'url' => 'https://example.com/untitled-product',
])->toArray();
expectTrue(findEntity($incompleteDocument, 'Product') === null, 'Incomplete Product entities should be omitted instead of emitted invalidly.');
expectTrue(findEntity($incompleteDocument, 'WebPage') === null, 'Incomplete WebPage entities should be omitted instead of emitted invalidly.');

$inlineFaqDocument = $engine->generate([
    'type' => 'page',
    'name' => 'Inline FAQ Page',
    'url' => 'https://example.com/inline-faq',
    'faq_items' => [['question' => 'Does inline FAQ work?', 'answer' => 'Yes.']],
])->toArray();
$inlineFaq = findEntity($inlineFaqDocument, 'FAQPage');
expectTrue(is_array($inlineFaq), 'Inline FAQ data should produce FAQPage.');
expectTrue(($inlineFaq['mainEntity'][0]['name'] ?? '') === 'Does inline FAQ work?', 'Inline FAQ should override provider FAQ data.');

$disabledEngine = new SchemaEngine(new Config([
    'enabled' => true,
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Demo',
    'organization' => ['name' => 'Acme Demo'],
    'schemas' => [
        'organization' => false,
        'website' => false,
        'local_business' => false,
        'webpage' => true,
        'breadcrumb' => false,
        'product' => false,
        'blog_posting' => false,
        'item_list' => false,
        'faq' => false,
        'related' => false,
    ],
]));
$disabledDocument = $disabledEngine->generate([
    'type' => 'product',
    'name' => 'Example Machine',
    'url' => 'https://example.com/products/example-machine',
])->toArray();
expectTrue(count($disabledDocument['@graph']) === 1, 'Only WebPage should remain after optional schemas are disabled.');
expectTrue(($disabledDocument['@graph'][0]['@type'] ?? '') === 'WebPage', 'Remaining entity should be WebPage.');
expectTrue(!isset($disabledDocument['@graph'][0]['publisher']), 'Disabled Organization should not leave a publisher reference.');
expectTrue(!isset($disabledDocument['@graph'][0]['isPartOf']), 'Disabled WebSite should not leave an isPartOf reference.');

$callbackProvider = new CallbackDataProvider([
    'faqs' => static function (array $page): array {
        return [['question' => 'Callback page?', 'answer' => (string) ($page['name'] ?? '')]];
    },
    'product_images' => static function (): array {
        return ['https://example.com/callback.jpg'];
    },
]);
$callbackDocument = (new SchemaEngine($config, $callbackProvider))->generate([
    'type' => 'product',
    'name' => 'Callback Product',
    'url' => 'https://example.com/callback-product',
])->toArray();
$callbackFaq = findEntity($callbackDocument, 'FAQPage');
$callbackProduct = findEntity($callbackDocument, 'Product');
expectTrue(($callbackFaq['mainEntity'][0]['acceptedAnswer']['text'] ?? '') === 'Callback Product', 'Callback provider should receive the current page.');
expectTrue(in_array('https://example.com/callback.jpg', $callbackProduct['image'] ?? [], true), 'Callback provider product image should be included.');

$unsafeGraph = $engine->generate([
    'type' => 'page',
    'name' => '</script><script>alert("x")</script>',
    'url' => 'https://example.com/security-test',
]);
$scriptTag = $unsafeGraph->toScriptTag();
expectTrue(stripos($scriptTag, '</script><script>') === false, 'Script-tag output must hex-escape HTML-significant JSON characters.');

$extension = new CallbackSchemaExtension(static function (array $page): array {
    return [
        '@type' => 'Service',
        '@id' => (string) $page['url'] . '#service',
        'name' => 'Example Service',
        'url' => (string) $page['url'],
    ];
});
$extensionDocument = (new SchemaEngine($config, null, null, [$extension]))->generate([
    'type' => 'page',
    'name' => 'Service Page',
    'url' => 'https://example.com/service',
])->toArray();
expectTrue(is_array(findEntity($extensionDocument, 'Service')), 'Custom schema extension should append entities without modifying the core.');

$dateUtc = Date::normalize('2026-08-20T08:30:00+00:00');
expectTrue($dateUtc === '2026-08-20T08:30:00+00:00', 'Date normalization should preserve an explicit UTC offset.');
$dateOffset = Date::normalize('2026-08-20T11:30:00+03:00');
expectTrue($dateOffset === '2026-08-20T11:30:00+03:00', 'Date normalization should preserve an explicit source offset.');
expectTrue(Date::normalize('not-a-date') === '', 'Invalid dates should be omitted.');

$validator = new GraphValidator();
$issues = $validator->validate($document);
expectTrue(!$validator->hasErrors($issues), 'Generated example document should pass internal graph validation.');
$aggregateOfferIssues = $validator->validate([
    '@context' => 'https://schema.org',
    '@graph' => [[
        '@type' => 'Product',
        '@id' => 'https://example.com/variable#product',
        'name' => 'Variable Example',
        'url' => 'https://example.com/variable',
        'offers' => [
            '@type' => 'AggregateOffer',
            'lowPrice' => '10.00',
            'highPrice' => '20.00',
            'priceCurrency' => 'USD',
            'offerCount' => 2,
        ],
    ]],
]);
expectTrue(!$validator->hasErrors($aggregateOfferIssues), 'Valid AggregateOffer should pass internal validation.');
$invalidIssues = $validator->validate([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'Product', '@id' => 'https://example.com/x#product', 'name' => 'X', 'url' => 'https://example.com/x'],
        ['@type' => 'Product', '@id' => 'https://example.com/x#product', 'name' => 'Y', 'url' => 'not-a-url'],
    ],
]);
expectTrue($validator->hasErrors($invalidIssues), 'Validator should reject duplicate IDs or invalid entity URLs.');

return true;
