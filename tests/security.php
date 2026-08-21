<?php
require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$patterns = [
    '/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/',
    '/\bAKIA[0-9A-Z]{16}\b/',
    '/\bghp_[A-Za-z0-9]{30,}\b/',
    '/\bgithub_pat_[A-Za-z0-9_]{20,}\b/',
    '/\bsk-proj-[A-Za-z0-9_-]{20,}\b/',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    if (
        strpos($path, DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR) !== false
        || strpos($path, DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR) !== false
        || $path === __FILE__
    ) {
        continue;
    }

    $extension = strtolower($file->getExtension());
    if (!in_array($extension, ['php', 'md', 'txt', 'json', 'yml', 'yaml', 'js', 'css', 'sql'], true)) {
        continue;
    }

    $content = file_get_contents($path);
    if (!is_string($content)) {
        continue;
    }

    foreach ($patterns as $pattern) {
        expectTrue(preg_match($pattern, $content) !== 1, 'Potential secret material found in ' . $path);
    }
}

return true;
