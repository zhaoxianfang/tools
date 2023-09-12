<?php

namespace zxf\Pay\Traits;

use Exception;
use zxf\Pay\WeChat\Crypto\AesGcm;
use zxf\Pay\WeChat\Crypto\Rsa;
use zxf\Pay\WeChat\Formatter;

/**
 * 支付回调
 */
trait CallbackTrait
{
    /**
     * 解析回调参数
     *
     * @return array|void
     * @throws Exception
     */
    protected function parseCallbackData()
    {
        $header = $this->getHeaders();

        $inWechatpaySignature = $header['WECHATPAY-SIGNATURE'] ?? '';// 请根据实际情况获取
        $inWechatpayTimestamp = $header['WECHATPAY-TIMESTAMP'] ?? '';// 请根据实际情况获取
        $inWechatpayNonce     = $header['WECHATPAY-NONCE'] ?? '';// 请根据实际情况获取
        $inBody               = file_get_contents('php://input') ?? $GLOBALS['HTTP_RAW_POST_DATA'];// 请根据实际情况获取，例如: file_get_contents('php://input');

        $apiV3Key = $this->config['v3_secret_key'];// 在商户平台上设置的APIv3密钥


        // 根据通知的平台证书序列号，查询本地平台证书文件，
        // $platformPublicKeyInstance = Rsa::from('file://' . $this->config['apiclient_cert'], Rsa::KEY_TYPE_PUBLIC);
        $platformPublicKeyInstance = openssl_get_publickey('file://' . $this->config['wechatpay_serial']);// 通过微信wechatpay安装包下载的证书文件内容

        // 检查通知时间偏移量，允许5分钟之内的偏移
        $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
        $verifiedStatus   = Rsa::verify(
        // 构造验签名串
            Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
            $inWechatpaySignature,
            $platformPublicKeyInstance
        );
        if ($timeOffsetStatus && $verifiedStatus) {
            // 转换通知的JSON文本消息为PHP Array数组
            $inBodyArray = (array)json_decode($inBody, true);
            // 使用PHP7的数据解构语法，从Array中解构并赋值变量
            [
                'resource' => [
                    'ciphertext'      => $ciphertext,
                    'nonce'           => $nonce,
                    'associated_data' => $aad,
                ],
            ] = $inBodyArray;

            // 加密文本消息解密
            $inBodyResource = AesGcm::decrypt($ciphertext, $apiV3Key, $nonce, $aad);
            // 把解密后的文本转换为PHP Array数组
            return (array)json_decode($inBodyResource, true);
        }

    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = [];
        if (!function_exists('apache_request_headers')) {
            $headerList = headers_list();
            foreach ($headerList as $header) {
                $header                        = explode(":", $header);
                $headers[array_shift($header)] = trim(implode(":", $header));
            }
            $headers = array_change_key_case($headers, CASE_UPPER);
        } else {
            $headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
        }
        return $headers;
    }
}