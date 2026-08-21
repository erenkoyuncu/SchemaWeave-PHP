<?php
$tests = [
    __DIR__ . '/core.php',
    __DIR__ . '/security.php',
];

foreach ($tests as $test) {
    try {
        $result = require $test;
        if ($result !== true) {
            throw new RuntimeException('Test did not return true: ' . basename($test));
        }
        echo 'PASS: ' . basename($test) . PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, 'FAIL: ' . basename($test) . ': ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

echo "SchemaWeave PHP test suite passed.\n";
