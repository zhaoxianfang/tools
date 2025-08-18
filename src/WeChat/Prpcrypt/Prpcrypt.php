<?php

namespace zxf\WeChat\Prpcrypt;

/**
 * 公众号消息 - 加解密
 */
class Prpcrypt
{
    public $key;

    /**
     * Prpcrypt constructor.
     */
    public function __construct(string $key)
    {
        $this->key = base64_decode("{$key}=");
    }

    /**
     * 对明文进行加密
     *
     * @param  string  $text  需要加密的明文
     * @param  string  $appid  公众号APPID
     * @return array
     */
    public function encrypt(string $text, string $appid)
    {
        try {
            $random = $this->getRandomStr();
            $iv = substr($this->key, 0, 16);
            $pkcEncoder = new PKCS7Encoder;
            $text = $pkcEncoder->encode($random.pack('N', strlen($text)).$text.$appid);
            $encrypted = openssl_encrypt($text, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);

            return [ErrorCode::$OK, $encrypted];
        } catch (\Exception $e) {
            return [ErrorCode::$EncryptAESError, null];
        }
    }

    /**
     * 对密文进行解密
     *
     * @param  string  $encrypted  需要解密的密文
     * @return array
     */
    public function decrypt(string $encrypted)
    {
        try {
            $iv = substr($this->key, 0, 16);
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        } catch (\Exception $e) {
            return [ErrorCode::$DecryptAESError, null];
        }
        try {
            $pkcEncoder = new PKCS7Encoder;
            $result = $pkcEncoder->decode($decrypted);
            if (strlen($result) < 16) {
                return [ErrorCode::$DecryptAESError, null];
            }
            $content = substr($result, 16, strlen($result));
            $len_list = unpack('N', substr($content, 0, 4));
            $xml_len = $len_list[1];

            return [0, substr($content, 4, $xml_len), substr($content, $xml_len + 4)];
        } catch (\Exception $e) {
            return [ErrorCode::$IllegalBuffer, null];
        }
    }

    /**
     * 随机生成16位字符串
     *
     *
     * @return string 生成的字符串
     */
    public function getRandomStr(string $str = '')
    {
        $str_pol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }

        return $str;
    }
}
