<?php
namespace SchemaWeave\Support;

final class Date
{
    public static function normalize($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        try {
            $date = new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            return '';
        }

        return $date->format(DATE_ATOM);
    }
}
