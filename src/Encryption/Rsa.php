<?php

namespace zxf\Encryption;

use Exception;

/**
 * 提供RSA公钥加密和私钥解密的功能。
 */
class Rsa
{
    /**
     * @var resource|null 私钥资源
     */
    private static $privateKey = null;

    /**
     * @var string|null 公钥字符串（PEM格式）
     */
    private static $publicKey = null;

    /**
     * 加载私钥。
     *
     * @param string $privateKeyPem 私钥PEM字符串
     *
     * @return bool 是否成功加载
     */
    public static function loadPrivateKey(string $privateKeyPem): bool
    {
        self::$privateKey = is_file($privateKeyPem) ? openssl_pkey_get_private(file_get_contents($privateKeyPem)) : $privateKeyPem;
        return self::$privateKey !== false;
    }

    /**
     * 加载公钥。
     *
     * @param string $publicKeyPem 公钥PEM字符串
     *
     * @return bool 是否成功加载
     */
    public static function loadPublicKey(string $publicKeyPem): bool
    {
        self::$publicKey = is_file($publicKeyPem) ? openssl_pkey_get_public(file_get_contents($publicKeyPem)) : $publicKeyPem;
        // 实际上公钥不需要通过openssl_pkey_get系列函数加载，
        // 因为在加密时可以直接使用PEM格式的字符串。
        // 但为了保持接口一致性，这里仍然返回一个bool值表示成功。
        return true;
    }

    /**
     * 保存密钥到文件。
     *
     * @param string $keyPem   密钥PEM字符串
     * @param string $filePath 文件路径
     *
     * @return bool 是否保存成功
     */
    public static function saveKeyToFile(string $keyPem, string $filePath): bool
    {
        // 确保目录存在且可写
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0700, true)) {
                return false; // 无法创建目录
            }
        } elseif (!is_writable($dir)) {
            return false; // 目录不可写
        }

        return file_put_contents($filePath, $keyPem) !== false;
    }

    /**
     * 生成RSA密钥对。
     *
     * @param int         $keyLength      密钥长度，默认为2048位
     * @param bool        $saveKeysToFile 是否将密钥保存到文件，默认为false
     * @param string|null $privateKeyFile 私钥文件路径（当$saveKeysToFile为true时有效）
     * @param string|null $publicKeyFile  公钥文件路径（当$saveKeysToFile为true时有效）
     *
     * @return array|bool
     */
    public static function generateKeyPair(int $keyLength = 2048, bool $saveKeysToFile = false, ?string $privateKeyFile = null, ?string $publicKeyFile = null): array|bool
    {
        $config = array(
            "private_key_bits" => $keyLength,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // 创建密钥对
        $result = openssl_pkey_new($config);
        if ($result === false) {
            return false;
        }

        // 提取私钥
        openssl_pkey_export($result, $privateKeyPem);
        self::$privateKey = openssl_pkey_get_private($privateKeyPem);

        // 提取公钥
        $publicKeyDetails = openssl_pkey_get_details($result);
        self::$publicKey  = $publicKeyDetails['key'];

        // 如果需要将密钥保存到文件
        if ($saveKeysToFile) {
            $privateKeySaveSuccess = self::saveKeyToFile($privateKeyPem, $privateKeyFile);
            $publicKeySaveSuccess  = self::saveKeyToFile($publicKeyDetails['key'], $publicKeyFile);

            return $privateKeySaveSuccess && $publicKeySaveSuccess;
        } else {
            // 不需要保存到文件，直接返回结果
            return [
                'privateKey' => $privateKeyPem,
                'publicKey'  => $publicKeyDetails['key'],
            ];
        }
    }

    /**
     * 使用公钥加密数据。
     *
     * @param string      $data          待加密的数据
     * @param string|null $publicKeyFile 公钥文件路径或PEM格式的字符串
     * @param int         $padding
     *
     * @return string|null 加密后的数据或null（如果失败）
     * @throws Exception
     */
    public static function encryptWithPublicKey(string $data, string $publicKeyFile = null, int $padding = OPENSSL_PKCS1_PADDING): ?string
    {
        if ($publicKeyFile) {
            self::loadPublicKey($publicKeyFile);
        }
        if (self::$publicKey === null) {
            return null; // 公钥未加载
        }
        if (
            !in_array($padding, [
                OPENSSL_PKCS1_PADDING,// 默认值：PKCS #1 填充。
                OPENSSL_SSLV23_PADDING, // ：SSLv23 填充。
                OPENSSL_NO_PADDING // ：无填充。
            ])
        ) {
            self::handleError("无效的填充参数");
        }

        openssl_public_encrypt($data, $encrypted, self::$publicKey, $padding);
        if ($encrypted === false) {
            return null; // 加密失败
        }

        // 对加密后的数据进行Base64编码，以便传输和存储
        return base64_encode($encrypted);
    }

    /**
     * 使用私钥解密数据。
     *
     * @param string      $encryptedData  加密的数据（Base64编码）
     * @param string|null $privateKeyFile 解密私钥文件路径或PEM格式的字符串
     * @param int         $padding
     *
     * @return string|null 解密后的数据或null（如果失败）
     * @throws Exception
     */
    public static function decryptWithPrivateKey(string $encryptedData, string $privateKeyFile = null, int $padding = OPENSSL_PKCS1_PADDING): ?string
    {
        if ($privateKeyFile) {
            self::loadPrivateKey($privateKeyFile);
        }
        if (self::$privateKey === null) {
            return null; // 私钥未加载
        }
        if (
            !in_array($padding, [
                OPENSSL_PKCS1_PADDING,// 默认值：PKCS #1 填充。
                OPENSSL_SSLV23_PADDING, // ：SSLv23 填充。
                OPENSSL_NO_PADDING // ：无填充。
            ])
        ) {
            self::handleError("无效的填充参数");
        }

        // 对加密的数据进行Base64解码
        $encryptedDataDecoded = base64_decode($encryptedData);
        if ($encryptedDataDecoded === false) {
            return null; // 解码失败
        }

        openssl_private_decrypt($encryptedDataDecoded, $decrypted, self::$privateKey, $padding);
        if ($decrypted === false) {
            return null; // 解密失败
        }

        return $decrypted;
    }

    /**
     * 处理错误。
     *
     * @param string         $errorMessage 错误信息
     * @param Throwable|null $exception    可选的异常对象
     *
     * @throws Exception 总是抛出异常（可以根据需要自定义错误处理逻辑）
     */
    public static function handleError(string $errorMessage, ?Throwable $exception = null): void
    {
        // 记录错误信息到日志文件（可以根据需要自定义日志记录方式）
        error_log($errorMessage . ($exception ? ': ' . $exception->getMessage() : ''));

        // 抛出异常，以便上层代码捕获并处理（或根据需要进行其他错误处理操作）
        throw new \Exception($errorMessage, $exception ? $exception->getCode() : 0, $exception);
    }
}
