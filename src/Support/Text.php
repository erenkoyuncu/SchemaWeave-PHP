<?php
namespace SchemaWeave\Support;

final class Text
{
    public static function clean($value, int $limit = 300): string
    {
        $text = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

        if (function_exists('wp_strip_all_tags')) {
            $text = \wp_strip_all_tags($text);
        } else {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Framework-agnostic fallback when WordPress is unavailable.
            $text = strip_tags($text);
        }

        $text = trim($text);
        $text = (string) preg_replace('/\s+/u', ' ', $text);

        if ($limit > 0 && function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
            $text = mb_substr($text, 0, $limit, 'UTF-8');
        } elseif ($limit > 0 && strlen($text) > $limit) {
            $text = substr($text, 0, $limit);
        }

        return trim($text);
    }
}
