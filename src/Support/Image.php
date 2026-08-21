<?php
namespace SchemaWeave\Support;

final class Image
{
    public static function absolute(string $baseUrl, $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if ($baseUrl === '') {
            return $value;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }
}
