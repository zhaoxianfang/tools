<?php

namespace zxf\Login\Helper;

class AES
{
    public static function encrypt(string $text, string $key, string $iv, int $option = OPENSSL_RAW_DATA): string
    {
        self::validateKey($key);
        self::validateIv($iv);

        return openssl_encrypt($text, self::getMode($key), $key, $option, $iv);
    }

    /**
     * @param  string|null  $method
     */
    public static function decrypt(string $cipherText, string $key, string $iv, int $option = OPENSSL_RAW_DATA, $method = null): string
    {
        self::validateKey($key);
        self::validateIv($iv);

        return openssl_decrypt($cipherText, $method ?: self::getMode($key), $key, $option, $iv);
    }

    /**
     * @param  string  $key
     * @return string
     */
    public static function getMode($key)
    {
        return 'aes-'.(8 * strlen($key)).'-cbc';
    }

    public static function validateKey(string $key)
    {
        if (! in_array(strlen($key), [16, 24, 32], true)) {
            throw new \InvalidArgumentException(sprintf('Key length must be 16, 24, or 32 bytes; got key len (%s).', strlen($key)));
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateIv(string $iv)
    {
        if (! empty($iv) && strlen($iv) !== 16) {
            throw new \InvalidArgumentException('IV length must be 16 bytes.');
        }
    }
}
