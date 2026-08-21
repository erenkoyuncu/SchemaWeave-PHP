<?php
$root = dirname(__DIR__);

spl_autoload_register(static function ($class) use ($root): void {
    $prefix = 'SchemaWeave\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $root . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

function expectTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function findEntity(array $document, string $type): ?array
{
    foreach (($document['@graph'] ?? []) as $entity) {
        if (is_array($entity) && ($entity['@type'] ?? '') === $type) {
            return $entity;
        }
    }

    return null;
}
