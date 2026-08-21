<?php
namespace SchemaWeave\Adapter;

use SchemaWeave\Contracts\UrlResolverInterface;

final class DefaultUrlResolver implements UrlResolverInterface
{
    public function resolve(array $page, array $config): string
    {
        if (!empty($page['url'])) {
            return (string) $page['url'];
        }

        $base = rtrim((string) ($config['base_url'] ?? ''), '/');
        $slug = trim((string) ($page['slug'] ?? ''), '/');

        if ($base === '') {
            return $slug === '' ? '' : '/' . $slug;
        }

        return $slug === '' ? $base . '/' : $base . '/' . $slug;
    }
}
