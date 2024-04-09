<?php

namespace zxf\Encryption;

use Exception;

/**
 * 提供RSA公钥加密和私钥解密的功能。
 */
class RSA
{
    /**
     * @var resource|null 私钥资源
     */
    private static $privateKey = null;

    /**
     * @var string|null 公钥字符串（PEM格式）
     */
    private static ?string $publicKey = null;

    /**
     * @var array 可选填充参数
     */
    private static array $padding = [
        OPENSSL_PKCS1_PADDING,// 默认值：PKCS #1 填充。
        OPENSSL_NO_PADDING, // ：无填充。
        OPENSSL_PKCS1_OAEP_PADDING,
    ];

    /**
     * 加载私钥。
     *
     * @param string $privateKeyPemOrString 私钥PEM文件路径或PEM格式的字符串
     *
     * @return bool 是否成功加载
     */
    public static function loadPrivateKey(string $privateKeyPemOrString): bool
    {
        self::$privateKey = is_file($privateKeyPemOrString) ? openssl_pkey_get_private(file_get_contents($privateKeyPemOrString)) : $privateKeyPemOrString;
        return self::$privateKey !== false;
    }

    /**
     * 加载公钥。
     *
     * @param string $publicKeyPemOrString 公钥PEM文件路径或PEM格式的字符串
     *
     * @return bool 是否成功加载
     */
    public static function loadPublicKey(string $publicKeyPemOrString): bool
    {
        self::$publicKey = is_file($publicKeyPemOrString) ? openssl_pkey_get_public(file_get_contents($publicKeyPemOrString)) : $publicKeyPemOrString;
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
    public static function saveKeyToFile(string $keyPem, string $filePath, $permissions = 0700): bool
    {
        // 确保目录存在且可写
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, $permissions, true)) {
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
     * @param int         $keyLength      密钥长度，默认为2048位；指定应该使用多少位来生成私钥
     * @param bool        $saveKeysToFile 是否将密钥保存到文件，默认为false
     * @param string|null $privateKeyFile 私钥文件路径（当$saveKeysToFile为true时有效）
     * @param string|null $publicKeyFile  公钥文件路径（当$saveKeysToFile为true时有效）
     * @param string      $digestAlg      摘要算法或签名哈希算法 sha512 、sha1、md5、sha384、sha256 等 支持的算法见 openssl_get_md_methods()
     *
     * @return array|bool
     */
    public static function generateKeyPair(int $keyLength = 2048, bool $saveKeysToFile = false, ?string $privateKeyFile = null, ?string $publicKeyFile = null, string $digestAlg = 'sha512'): array|bool
    {
        if (!in_array($digestAlg, openssl_get_md_methods())) {
            return false;
        }

        $config = array(
            "digest_alg"       => $digestAlg,// 摘要算法或签名哈希算法
            "private_key_bits" => $keyLength,
            "private_key_type" => OPENSSL_KEYTYPE_RSA, // 选择在创建 CSR 时应该使用哪些扩展,此类为Rsa类，所以此处固定为OPENSSL_KEYTYPE_RSA
        );

        // 设置随机种子
        openssl_random_pseudo_bytes(32, $crypto_strong);
        if (!$crypto_strong) {
            self::handleError("Could not generate a strong key. Please try again.");
        }

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
                'private_key' => $privateKeyPem,
                'public_key'  => $publicKeyDetails['key'],
            ];
        }
    }

    /**
     * 使用公钥加密数据。
     *
     * @param string      $data          待加密的数据
     * @param string      $outputFormat  输出格式（'base64'或'hex'）
     * @param string|null $publicKeyFile 公钥文件路径或PEM格式的字符串
     * @param int         $padding       填充参数,默认为OPENSSL_PKCS1_PADDING
     *
     * @return string|null 加密后的数据或null（如果失败）
     * @throws Exception
     */
    public static function encryptWithPublicKey(string $data, string $outputFormat = 'base64', string $publicKeyFile = null, int $padding = OPENSSL_PKCS1_PADDING): ?string
    {
        if ($publicKeyFile) {
            self::loadPublicKey($publicKeyFile);
        }
        if (self::$publicKey === null) {
            return null; // 公钥未加载
        }
        if (!in_array($padding, self::$padding)) {
            self::handleError("无效的填充参数");
        }

        openssl_public_encrypt($data, $encrypted, self::$publicKey, $padding);
        if ($encrypted === false) {
            return null; // 加密失败
        }

        // 对加密后的数据进行Base64编码，以便传输和存储
        // 根据输出格式返回结果
        return $outputFormat === 'hex' ? bin2hex($encrypted) : base64_encode($encrypted);
    }

    /**
     * 使用私钥解密数据。
     *
     * @param string      $encryptedData  加密的数据
     * @param string      $inputFormat    输入格式（'base64'或'hex'）
     * @param string|null $privateKeyFile 解密私钥文件路径或PEM格式的字符串
     * @param int         $padding        填充参数,默认为OPENSSL_PKCS1_PADDING
     *
     * @return string|null 解密后的数据或null（如果失败）
     * @throws Exception
     */
    public static function decryptWithPrivateKey(string $encryptedData, string $inputFormat = 'base64', string $privateKeyFile = null, int $padding = OPENSSL_PKCS1_PADDING): ?string
    {
        if ($privateKeyFile) {
            self::loadPrivateKey($privateKeyFile);
        }
        if (self::$privateKey === null) {
            return null; // 私钥未加载
        }
        if (!in_array($padding, self::$padding)) {
            self::handleError("无效的填充参数");
        }

        // 根据输入格式转换数据
        $encryptedDataDecoded = $inputFormat === 'hex' ? hex2bin($encryptedData) : base64_decode($encryptedData);
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
