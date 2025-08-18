<?php

declare(strict_types=1);

namespace zxf\Dom;

class Encoder
{
    public static function convertToHtmlEntities(string $string, string $encoding): string
    {
        // handling HTML entities via mbstring is deprecated in PHP 8.2
        if (function_exists('mb_convert_encoding') && PHP_VERSION_ID < 80200) {
            return mb_convert_encoding($string, 'HTML-ENTITIES', $encoding);
        }

        if ($encoding !== 'UTF-8') {
            $string = iconv($encoding, 'UTF-8//IGNORE', $string);
        }

        return preg_replace_callback('/[\x80-\xFF]+/', [__CLASS__, 'htmlEncodingCallback'], $string);
    }

    /**
     * @param  string[]  $matches
     */
    private static function htmlEncodingCallback(array $matches): string
    {
        $characterIndex = 1;
        $entities = '';

        $codes = unpack('C*', htmlentities($matches[0], ENT_COMPAT, 'UTF-8'));

        while (isset($codes[$characterIndex])) {
            if ($codes[$characterIndex] < 0x80) {
                $entities .= chr($codes[$characterIndex++]);

                continue;
            }

            if ($codes[$characterIndex] >= 0xF0) {
                $code = (($codes[$characterIndex++] - 0xF0) << 18) + (($codes[$characterIndex++] - 0x80) << 12) + (($codes[$characterIndex++] - 0x80) << 6) + $codes[$characterIndex++] - 0x80;
            } elseif ($codes[$characterIndex] >= 0xE0) {
                $code = (($codes[$characterIndex++] - 0xE0) << 12) + (($codes[$characterIndex++] - 0x80) << 6) + $codes[$characterIndex++] - 0x80;
            } else {
                $code = (($codes[$characterIndex++] - 0xC0) << 6) + $codes[$characterIndex++] - 0x80;
            }

            $entities .= '&#'.$code.';';
        }

        return $entities;
    }
}
