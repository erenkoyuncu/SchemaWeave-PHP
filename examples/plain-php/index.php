<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use SchemaWeave\Adapter\ArrayDataProvider;
use SchemaWeave\Config;
use SchemaWeave\SchemaEngine;

$config = new Config([
    'base_url' => 'https://example.com',
    'site_name' => 'Acme Industrial',
    'default_language' => 'en-US',
    'organization' => [
        'name' => 'Acme Industrial',
        'description' => 'Example industrial solutions company used for demonstration.',
        'email' => 'hello@example.com',
        'telephone' => '+1 555 0100',
        'logo' => '/assets/logo.png',
        'same_as' => [
            'https://www.linkedin.com/company/example/',
        ],
    ],
]);

$provider = new ArrayDataProvider([
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'https://example.com/'],
        ['name' => 'Products', 'url' => 'https://example.com/products'],
        ['name' => 'Example Packaging Machine', 'url' => 'https://example.com/products/example-packaging-machine'],
    ],
    'faqs' => [
        ['question' => 'Is this real product data?', 'answer' => 'No. All values in this example are fictional.'],
    ],
    'product_images' => [
        '/assets/products/example-machine-side.jpg',
    ],
]);

$engine = new SchemaEngine($config, $provider);

$graph = $engine->generate([
    'type' => 'product',
    'name' => 'Example Packaging Machine',
    'url' => 'https://example.com/products/example-packaging-machine',
    'description' => 'A fictional product used only to demonstrate structured data generation.',
    'image' => '/assets/products/example-machine.jpg',
    'brand' => 'Acme Demo Brand',
    'sku' => 'DEMO-1000',
]);

echo $graph->toScriptTag();
