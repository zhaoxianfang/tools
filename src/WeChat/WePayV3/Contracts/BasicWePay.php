<?php

// +----------------------------------------------------------------------
// | WeChatDeveloper
// +----------------------------------------------------------------------

namespace zxf\WeChat\WePayV3\Contracts;

use zxf\WeChat\WeChat\Contracts\Tools;
use zxf\WeChat\WeChat\Exceptions\InvalidArgumentException;
use zxf\WeChat\WeChat\Exceptions\InvalidDecryptException;
use zxf\WeChat\WeChat\Exceptions\InvalidResponseException;
use zxf\WeChat\WePayV3\Cert;

/**
 * 微信支付基础类
 * Class BasicWePay
 * @package WePayV3
 */
abstract class BasicWePay
{
    /**
     * 接口基础地址
     * @var string
     */
    protected $base = 'https://api.mch.weixin.qq.com';

    /**
     * 实例对象静态缓存
     * @var array
     */
    static $cache = [];

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'appid'        => '', // 微信绑定APPID，需配置
        'mch_id'       => '', // 微信商户编号，需要配置
        'mch_v3_key'   => '', // 微信商户密钥，需要配置
        'cert_serial'  => '', // 商户证书序号，无需配置
        'cert_public'  => '', // 商户公钥内容，需要配置
        'cert_private' => '', // 商户密钥内容，需要配置
    ];

    /**
     * BasicWePayV3 constructor.
     * @param array $options [mch_id, mch_v3_key, cert_public, cert_private]
     */
    public function __construct(array $options = [])
    {
        if (empty($options['mch_id'])) {
            throw new InvalidArgumentException("Missing Config -- [mch_id]");
        }
        if (empty($options['mch_v3_key'])) {
            throw new InvalidArgumentException("Missing Config -- [mch_v3_key]");
        }
        if (empty($options['cert_public'])) {
            throw new InvalidArgumentException("Missing Config -- [cert_public]");
        }
        if (empty($options['cert_private'])) {
            throw new InvalidArgumentException("Missing Config -- [cert_private]");
        }

        if (stripos($options['cert_public'], '-----BEGIN CERTIFICATE-----') === false) {
            if (file_exists($options['cert_public'])) {
                $options['cert_public'] = file_get_contents($options['cert_public']);
            } else {
                throw new InvalidArgumentException("File Non-Existent -- [cert_public]");
            }
        }

        if (stripos($options['cert_private'], '-----BEGIN PRIVATE KEY-----') === false) {
            if (file_exists($options['cert_private'])) {
                $options['cert_private'] = file_get_contents($options['cert_private']);
            } else {
                throw new InvalidArgumentException("File Non-Existent -- [cert_private]");
            }
        }

        $this->config['appid'] = isset($options['appid']) ? $options['appid'] : '';
        $this->config['mch_id'] = $options['mch_id'];
        $this->config['mch_v3_key'] = $options['mch_v3_key'];
        $this->config['cert_public'] = $options['cert_public'];
        $this->config['cert_private'] = $options['cert_private'];

        if (empty($options['cert_serial'])) {
            $this->config['cert_serial'] = openssl_x509_parse($this->config['cert_public'], true)['serialNumberHex'];
        } else {
            $this->config['cert_serial'] = $options['cert_serial'];
        }
        if (empty($this->config['cert_serial'])) {
            throw new InvalidArgumentException('Failed to parse certificate public key');
        }

        if (!empty($options['cache_path'])) {
            Tools::$cache_path = $options['cache_path'];
        }

        // 服务商参数支持
//        if (!empty($options['sp_appid'])) {
//            $this->config['sp_appid'] = $options['sp_appid'];
//        }
//        if (!empty($options['sp_mchid'])) {
//            $this->config['sp_mchid'] = $options['sp_mchid'];
//        }
//        if (!empty($options['sub_appid'])) {
//            $this->config['sub_appid'] = $options['sub_appid'];
//        }
//        if (!empty($options['sub_mch_id'])) {
//            $this->config['sub_mch_id'] = $options['sub_mch_id'];
//        }
    }

    /**
     * 静态创建对象
     * @param array $config
     * @return static
     */
    public static function instance($config)
    {
        $key = md5(get_called_class() . serialize($config));
        if (isset(self::$cache[$key])) return self::$cache[$key];
        return self::$cache[$key] = new static($config);
    }

    /**
     * 模拟发起请求
     * @param string $method 请求访问
     * @param string $pathinfo 请求路由
     * @param string $jsondata 请求数据
     * @param boolean $verify 是否验证
     * @param boolean $isjson 返回JSON
     * @return array|string
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function doRequest($method, $pathinfo, $jsondata = '', $verify = false, $isjson = true)
    {
        list($time, $nonce) = [time(), uniqid() . rand(1000, 9999)];
        $signstr = join("\n", [$method, $pathinfo, $time, $nonce, $jsondata, '']);

        // 生成数据签名TOKEN
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->config['mch_id'], $nonce, $time, $this->config['cert_serial'], $this->signBuild($signstr)
        );
        $location = (preg_match('|^https?://|', $pathinfo) ? '' : $this->base) . $pathinfo;
        list($header, $content) = $this->_doRequestCurl($method, $location, [
            'data' => $jsondata, 'header' => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: https://thinkadmin.top',
                "Authorization: WECHATPAY2-SHA256-RSA2048 {$token}",
                "Wechatpay-Serial: {$this->config['cert_serial']}"
            ],
        ]);

        if ($verify) {
            $headers = [];
            foreach (explode("\n", $header) as $line) {
                if (stripos($line, 'Wechatpay') !== false) {
                    list($name, $value) = explode(':', $line);
                    list(, $keys) = explode('wechatpay-', strtolower($name));
                    $headers[$keys] = trim($value);
                }
            }
            try {
                if (empty($headers)) {
                    return $isjson ? json_decode($content, true) : $content;
                }
                $string = join("\n", [$headers['timestamp'], $headers['nonce'], $content, '']);
                if (!$this->signVerify($string, $headers['signature'], $headers['serial'])) {
                    throw new InvalidResponseException('验证响应签名失败');
                }
            } catch (\Exception $exception) {
                throw new InvalidResponseException($exception->getMessage(), $exception->getCode());
            }
        }

        return $isjson ? json_decode($content, true) : $content;
    }

    /**
     * 通过CURL模拟网络请求
     * @param string $method 请求方法
     * @param string $location 请求方法
     * @param array $options 请求参数 [data, header]
     * @return array [header,content]
     */
    private function _doRequestCurl($method, $location, $options = [])
    {
        $curl = curl_init();
        // POST数据设置
        if (strtolower($method) === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data']);
        }
        // CURL头信息设置
        if (!empty($options['header'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['header']);
        }
        curl_setopt($curl, CURLOPT_URL, $location);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        return [substr($content, 0, $headerSize), substr($content, $headerSize)];
    }

    /**
     * 生成数据签名
     * @param string $data 签名内容
     * @return string
     */
    protected function signBuild($data)
    {
        $pkeyid = openssl_pkey_get_private($this->config['cert_private']);
        openssl_sign($data, $signature, $pkeyid, 'sha256WithRSAEncryption');
        return base64_encode($signature);
    }

    /**
     * 验证内容签名
     * @param string $data 签名内容
     * @param string $sign 原签名值
     * @param string $serial 证书序号
     * @return int
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    protected function signVerify($data, $sign, $serial = '')
    {
        $cert = $this->tmpFile($serial);
        if (empty($cert)) {
            Cert::instance($this->config)->download();
            $cert = $this->tmpFile($serial);
        }
        return @openssl_verify($data, base64_decode($sign), openssl_x509_read($cert), 'sha256WithRSAEncryption');
    }

    /**
     * 写入或读取临时文件
     * @param string $name
     * @param null|string $content
     * @return string
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    protected function tmpFile($name, $content = null)
    {
        if (is_null($content)) {
            return base64_decode(Tools::getCache($name) ?: '');
        } else {
            return Tools::setCache($name, base64_encode($content), 7200);
        }
    }

    /**
     * RSA加密处理
     * @param string $string
     * @return string
     * @throws \WeChat\Exceptions\InvalidDecryptException
     */
    protected function rsaEncode($string)
    {
        $publicKey = file_get_contents($this->config['cert_public']);
        if (openssl_public_encrypt($string, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            return base64_encode($encrypted);
        } else {
            throw new InvalidDecryptException('Rsa Encrypt Error.');
        }
    }

    /**
     * RSA 解密处理
     * @param string $string
     * @return string
     * @throws \WeChat\Exceptions\InvalidDecryptException
     */
    protected function rsaDecode($string)
    {
        $private = file_get_contents($this->config['cert_private']);
        if (openssl_private_decrypt(base64_decode($string), $content, $private, OPENSSL_PKCS1_OAEP_PADDING)) {
            return $content;
        } else {
            throw new InvalidDecryptException('Rsa Decrypt Error.');
        }
    }
}