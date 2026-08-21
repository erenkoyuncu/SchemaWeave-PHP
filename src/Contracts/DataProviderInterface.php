<?php
namespace SchemaWeave\Contracts;

interface DataProviderInterface
{
    public function getBreadcrumbItems(array $page): array;
    public function getCollectionItems(array $page): array;
    public function getFaqItems(array $page): array;
    public function getRelatedItems(array $page): array;
    public function getProductImages(array $page): array;
}
