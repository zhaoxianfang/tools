<?php

namespace zxf\Encryption;

use Exception;

class AES
{
    private string $key;
    private string $iv;
    private mixed $algorithm;
    private mixed $padding;

    // 此类中限制 支持的几种加密算法
    private array $algorithmMap = [
        'aes-128-cbc',
        'aes-192-cbc',
        'aes-256-cbc',
        'aes-128-ecb',
        'aes-192-ecb',
        'aes-256-ecb',
    ];

    /**
     * @var array 可选填充参数
     */
    private static array $paddingMap = [
        OPENSSL_PKCS1_PADDING,// 默认值：PKCS #1 填充。
        OPENSSL_NO_PADDING, // ：无填充。
        OPENSSL_PKCS1_PADDING,
        OPENSSL_PKCS1_OAEP_PADDING,
    ];

    /**
     * 构造函数
     *
     * @param string $key       密钥，必须是16、24或32位
     * @param string $iv        初始化向量，必须是16字节的二进制字符串
     * @param string $algorithm 加密算法，默认为aes-256-cbc
     * @param int    $padding   填充模式，默认为OPENSSL_PKCS1_PADDING
     *
     * @throws Exception 当参数不符合要求时抛出异常
     */
    public function __construct(string $key, string $iv, string $algorithm = 'aes-256-cbc', int $padding = OPENSSL_PKCS1_PADDING)
    {
        // 检查openssl扩展是否可用
        if (!extension_loaded('openssl')) {
            throw new Exception("OpenSSL extension is not available. Please enable it in your PHP configuration.");
        }

        // 检查密钥和IV的长度
        $keyLengths = [16, 24, 32];
        if (!in_array(strlen($key), $keyLengths, true)) {
            throw new Exception("Invalid key length. Key must be " . implode(', ', $keyLengths) . " bytes.");
        }

        // 判断是否是ECB模式
        if (str_contains($algorithm, 'ecb')) {
            $iv = ''; // ECB模式下IV为空
        } else {
            if (strlen($iv) !== 16) {
                throw new Exception("Invalid IV length. IV must be 16 bytes.");
            }
        }

        $this->key = $key;
        $this->iv  = $iv;

        // 检查加密算法的合法性
        $supportedAlgorithms = openssl_get_cipher_methods();
        if (!in_array($algorithm, $supportedAlgorithms, true)) {
            throw new Exception("Unsupported encryption algorithm.");
        }

        $this->algorithm = $algorithm;

        // 检查填充模式的合法性
        if (!in_array($padding, self::$paddingMap, true)) {
            throw new Exception("Unsupported padding mode.");
        }

        $this->padding = $padding;
    }

    /**
     * 加密数据
     *
     * @param string $data 要加密的数据
     *
     * @return string 加密后的数据
     *
     * @throws Exception 当加密失败时抛出异常
     */
    public function encrypt(string $data): string
    {
        $cipherText = openssl_encrypt($data, $this->algorithm, $this->key, 0, $this->iv, $this->padding);

        if ($cipherText === false) {
            throw new Exception("Encryption failed: " . openssl_error_string());
        }

        return $cipherText;
    }

    /**
     * 解密数据
     *
     * @param string $cipherText 要解密的数据
     *
     * @return string 解密后的数据
     *
     * @throws Exception 当解密失败时抛出异常
     */
    public function decrypt(string $cipherText): string
    {
        $plainText = openssl_decrypt($cipherText, $this->algorithm, $this->key, 0, $this->iv, $this->padding);

        if ($plainText === false) {
            throw new Exception("Decryption failed: " . openssl_error_string());
        }

        return $plainText;
    }
}