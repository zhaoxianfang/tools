<?php

namespace zxf\Pay\Traits;

use Exception;
use zxf\Pay\WeChat\Crypto\Rsa;
use zxf\Pay\WeChat\Formatter;
use zxf\tools\Random;
use zxf\tools\Str;

/**
 * 获取微信支付v3 签名信息
 * https://developers.weixin.qq.com/community/develop/article/doc/000a62f183cea8267b3a56d1856013
 */
trait SignTrait
{

    /**
     * 微信支付v3 生成请求头的 Authorization 部分
     *
     * @param string $url       接口请求地址
     * @param string $method    请求方式 GET/POST
     * @param mixed  $body      获取请求中的请求报文主体（request body）
     *                          请求方法为GET时，报文主体为空。
     *                          当请求方法为POST或PUT时，请使用真实发送的JSON报文。
     *                          图片上传API，请使用meta对应的JSON报文。
     *
     * @return string
     * @throws Exception
     */
    public function v3GetWechatAuthorization(string $url, string $method = 'GET', $body = ''): string
    {
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new \RuntimeException("当前PHP环境不支持SHA256withRSA");
        }

        $config            = $this->config->toArray();
        $mchPrivateKey     = $config['mch_private_cert'] ?? null; // 商户私钥 内容字符或者文件路径
        $mchPublicCertPath = $config['mch_public_cert'] ?? null; // 商户公钥 内容字符或者文件路径
        $mchid             = $config['mchid'] ?? null; // 商户ID

        $url_parts     = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));

        //私钥地址
        $mchPrivateKey = $this->getPrivateCert($mchPrivateKey);
        //当前时间戳
        $timestamp = time();
        //随机字符串
        $nonce  = Formatter::nonce();
        $method = strtoupper($method);
        //POST请求时 需要 转JSON字符串
        $body    = $method == 'GET' ? '' : (!empty($body) ? json_encode($body) : '');
        $message = $method . "\n" .
                   $canonical_url . "\n" .
                   $timestamp . "\n" .
                   $nonce . "\n" .
                   $body . "\n";

        //生成签名
        openssl_sign($message, $raw_sign, openssl_get_privatekey($mchPrivateKey), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        // 商户证书序列号
        $serial_no = $this->getSerialNo($mchPublicCertPath);

        //生成Authorization
        return sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",serial_no="%s",nonce_str="%s",timestamp="%d",signature="%s"', $mchid, $serial_no, $nonce, $timestamp, $sign);
    }

    /**
     * 创建v3 接口请求头
     *
     * @param string            $url
     * @param string            $method
     * @param string|array|null $body
     *
     * @return string[]
     * @throws Exception
     */
    public function v3CreateHeader(string $url = '', string $method = 'POST', array|string|null $body = ''): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'User-Agent:*/*',
            'Authorization' => $this->v3GetWechatAuthorization($url, $method, $body),
        ];
    }

    /**
     * 获取证书 序号 对比发现返回的和 getSerialNo() 方法的返回值 不太对
     *
     * @return mixed
     * @throws Exception
     * @deprecated 请使用 getSerialNo() 方法
     */
    public function getCert()
    {
        $url      = 'https://api.mch.weixin.qq.com/v3/certificates';
        $header   = $this->v3CreateHeader($url, 'GET', '');
        $header[] = 'User-Agent:' . $this->config->get('mchid');
        return $this->http->setHeader($header, false)->get($url); // 返回数组中包含了证书序号 serial_no
    }

    // 获取商户公钥证书内容
    private function getPublicCert(string $key): string
    {
        return Str::endsWith($key, ['cer', 'crt', 'pem']) ? file_get_contents($key) : $key;
    }

    // 获取商户私钥证书内容
    private function getPrivateCert(string $key): string
    {
        if (Str::endsWith($key, ['crt', 'pem'])) {
            return file_get_contents($key);
        }

        return "-----BEGIN RSA PRIVATE KEY-----\n" .
               wordwrap($key, 64, "\n", true) .
               "\n-----END RSA PRIVATE KEY-----";
    }

    // 商户Api证书序列号
    private function getSerialNo(string $mchPublicCertPath): string
    {
        $info = openssl_x509_parse($this->getPublicCert($mchPublicCertPath));

        if (false === $info || !isset($info['serialNumberHex'])) {
            throw new Exception('公钥证书读取失败，请检查配置文件 是否正确');
        }

        return strtoupper($info['serialNumberHex'] ?? '');
    }

    /**
     * 获取JsApi支付签名参数
     *
     * @param string|null $prepay_id 预支付交易会话标识
     *
     * @return array
     * @throws Exception
     */
    public function getJsApiSignParams(?string $prepay_id = ''): array
    {
        $private_file_url           = 'file://' . realpath($this->config['mch_private_cert']);
        $merchantPrivateKeyInstance = Rsa::from($private_file_url);
        $prepay_id                  = $prepay_id ?: 'wx' . date('YmdHis') . Random::build('alnum', 10);
        $params                     = [
            'appId'     => $this->config['appid'],
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr'  => Formatter::nonce(),
            'package'   => 'prepay_id=' . $prepay_id,
        ];
        $params['paySign']          = Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        );
        $params['signType']         = 'RSA'; // 签名类型，默认为RSA，仅支持RSA。

        return $params;
    }

    /**
     * 获取App支付签名参数
     *
     * @param string|null $prepayid 预支付交易会话标识
     *
     * @return array
     * @throws Exception
     */
    public function getAppSignParams(?string $prepayid = ''): array
    {
        $merchantPrivateKeyInstance = Rsa::from($this->config['mch_private_cert']);
        $prepayid                   = $prepayid ?: 'wx' . date('YmdHis') . Random::build('alnum', 10);
        $params                     = [
            'appId'     => $this->config['app_id'],
            'partnerid' => $this->config['mchid'],
            'prepayid'  => $prepayid,
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr'  => Formatter::nonce(),
            'package'   => 'Sign=WXPay', // 固定值Sign=WXPay
        ];
        $params['sign']             = Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        );
        return $params;
    }

    private function getEncrypt($str)
    {
        //$str是待加密字符串
        $public_key = $this->getPublicCert($this->config['mch_public_cert']);
        $encrypted  = '';
        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new Exception('encrypt failed');
        }
        return $sign;
    }
}