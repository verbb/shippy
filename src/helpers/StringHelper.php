<?php
namespace verbb\shippy\helpers;

class StringHelper
{
    // Static Methods
    // =========================================================================

    public static function toPascalCase(string $str): string
    {
        $words = self::toWords($str, true, true);

        return implode('', array_map('ucfirst', $words));
    }

    public static function toWords(string $str, bool $lower = false, bool $removePunctuation = false): array
    {
        // Convert CamelCase to multiple words
        // Regex copied from Inflector::camel2words(), but without dropping punctuation
        $str = preg_replace('/(?<!\p{Lu})(\p{Lu})|(\p{Lu})(?=\p{Ll})/u', ' \0', $str);

        if ($lower) {
            // Make it lowercase
            $str = mb_strtolower($str);
        }

        if ($removePunctuation) {
            $str = str_replace(['.', '_', '-'], ' ', $str);
        }

        // Remove inner-word punctuation.
        $str = preg_replace('/[\'"‘’“”\[\]\(\)\{\}:]/u', '', $str);

        // Split on the words and return
        return static::splitOnWords($str);
    }

    public static function splitOnWords(string $str): array
    {
        // Split on anything that is not alphanumeric, or a period, underscore, or hyphen.
        // Reference: http://www.regular-expressions.info/unicode.html
        preg_match_all('/[\p{L}\p{N}\p{M}\._-]+/u', $str, $matches);

        return self::filterEmptyStringsFromArray($matches[0]);
    }

    public static function filterEmptyStringsFromArray(array $array): array
    {
        return array_filter($array, function($value): bool {
            return $value !== '';
        });
    }
}