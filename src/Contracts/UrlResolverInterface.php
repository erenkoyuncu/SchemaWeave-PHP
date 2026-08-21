<?php
namespace SchemaWeave\Contracts;

interface UrlResolverInterface
{
    public function resolve(array $page, array $config): string;
}
