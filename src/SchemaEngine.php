<?php
namespace SchemaWeave;

use SchemaWeave\Adapter\DefaultUrlResolver;
use SchemaWeave\Adapter\NullDataProvider;
use SchemaWeave\Contracts\DataProviderInterface;
use SchemaWeave\Contracts\SchemaExtensionInterface;
use SchemaWeave\Contracts\UrlResolverInterface;
use SchemaWeave\Schema\BlogPostingSchema;
use SchemaWeave\Schema\BreadcrumbSchema;
use SchemaWeave\Schema\FAQSchema;
use SchemaWeave\Schema\ItemListSchema;
use SchemaWeave\Schema\LocalBusinessSchema;
use SchemaWeave\Schema\OrganizationSchema;
use SchemaWeave\Schema\ProductSchema;
use SchemaWeave\Schema\WebPageSchema;
use SchemaWeave\Schema\WebsiteSchema;

final class SchemaEngine
{
    private Config $config;
    private DataProviderInterface $provider;
    private UrlResolverInterface $urlResolver;
    /** @var SchemaExtensionInterface[] */
    private array $extensions;

    public function __construct(
        Config $config,
        ?DataProviderInterface $provider = null,
        ?UrlResolverInterface $urlResolver = null,
        array $extensions = []
    ) {
        $this->config = $config;
        $this->provider = $provider ?? new NullDataProvider();
        $this->urlResolver = $urlResolver ?? new DefaultUrlResolver();
        $this->extensions = [];

        foreach ($extensions as $extension) {
            if ($extension instanceof SchemaExtensionInterface) {
                $this->extensions[] = $extension;
            }
        }
    }

    public function generate(array $page): SchemaGraph
    {
        if (!$this->config->isEnabled()) {
            return new SchemaGraph([]);
        }

        $config = $this->config->all();
        $url = $this->urlResolver->resolve($page, $config);
        $language = (string) ($page['language'] ?? ($config['default_language'] ?? 'en-US'));
        $graph = [];

        if ($this->config->schemaEnabled('organization')) {
            $organization = OrganizationSchema::build($config);
            if ($organization) {
                $graph[] = $organization;
            }
        }

        if ($this->config->schemaEnabled('website')) {
            $website = WebsiteSchema::build($config);
            if ($website) {
                $graph[] = $website;
            }
        }

        if (
            $this->config->schemaEnabled('local_business')
            && !empty($page['include_locations'])
        ) {
            foreach (LocalBusinessSchema::buildAll($config) as $location) {
                $graph[] = $location;
            }
        }

        $breadcrumb = null;
        if ($this->config->schemaEnabled('breadcrumb')) {
            $breadcrumbs = $this->provider->getBreadcrumbItems($page);
            if (empty($breadcrumbs) && $url !== '') {
                $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
                if ($baseUrl !== '') {
                    $breadcrumbs[] = [
                        'name' => (string) ($config['home_name'] ?? 'Home'),
                        'url' => $baseUrl . '/',
                    ];
                }
                if (!empty($page['name'])) {
                    $breadcrumbs[] = [
                        'name' => (string) $page['name'],
                        'url' => $url,
                    ];
                }
            }

            $breadcrumb = $url !== '' ? BreadcrumbSchema::build($breadcrumbs, $url) : null;
            if ($breadcrumb) {
                $graph[] = $breadcrumb;
            }
        }

        $pageEntity = null;
        if ($this->config->schemaEnabled('webpage')) {
            $pageEntity = WebPageSchema::build(
                $page,
                $config,
                $url,
                $breadcrumb['@id'] ?? null
            );
        }

        $type = (string) ($page['type'] ?? 'page');

        if ($type === 'product' && $this->config->schemaEnabled('product')) {
            $product = ProductSchema::build(
                $page,
                $config,
                $url,
                $this->provider->getProductImages($page)
            );
            if ($product !== null) {
                if ($pageEntity !== null) {
                    $pageEntity['mainEntity'] = ['@id' => $product['@id']];
                }
                $graph[] = $product;
            }
        } elseif ($type === 'blog_post' && $this->config->schemaEnabled('blog_posting')) {
            $article = BlogPostingSchema::build($page, $config, $url);
            if ($article !== null) {
                if ($pageEntity !== null) {
                    $pageEntity['mainEntity'] = ['@id' => $article['@id']];
                }
                $graph[] = $article;
            }
        } elseif (
            $type === 'collection'
            && $this->config->schemaEnabled('item_list')
            && $pageEntity !== null
        ) {
            $pageEntity['mainEntity'] = ItemListSchema::build(
                $this->provider->getCollectionItems($page)
            );
        }

        if ($this->config->schemaEnabled('faq')) {
            $faqRows = array_key_exists('faq_items', $page) && is_array($page['faq_items'])
                ? $page['faq_items']
                : $this->provider->getFaqItems($page);

            $faq = FAQSchema::build(
                $faqRows,
                $url,
                $language
            );
            if ($faq) {
                if ($pageEntity !== null) {
                    if (!isset($pageEntity['hasPart']) || !is_array($pageEntity['hasPart'])) {
                        $pageEntity['hasPart'] = [];
                    }
                    $pageEntity['hasPart'][] = ['@id' => $faq['@id']];
                }
                $graph[] = $faq;
            }
        }

        if ($this->config->schemaEnabled('related') && $pageEntity !== null) {
            $related = $this->provider->getRelatedItems($page);
            if (!empty($related)) {
                $references = [];
                foreach ($related as $item) {
                    if (!is_array($item) || empty($item['url'])) {
                        continue;
                    }
                    $references[] = [
                        '@type' => $item['schema_type'] ?? 'WebPage',
                        '@id' => (string) $item['url'] . '#webpage',
                        'url' => (string) $item['url'],
                        'name' => (string) ($item['name'] ?? ''),
                    ];
                }
                if (!empty($references)) {
                    $pageEntity['subjectOf'] = $references;
                }
            }
        }

        if ($pageEntity !== null) {
            $graph[] = $pageEntity;
        }

        foreach ($this->extensions as $extension) {
            foreach ($extension->build($page, $config) as $entity) {
                if (is_array($entity) && !empty($entity['@type'])) {
                    $graph[] = $entity;
                }
            }
        }

        return new SchemaGraph($graph);
    }
}
