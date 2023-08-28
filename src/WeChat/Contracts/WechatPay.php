<?php

namespace zxf\WeChat\Contracts;

use zxf\WeChat\WeChatBase;

/**
 * 微信支付基础类
 */
class WechatPay extends WeChatBase
{
    // 微信支付请求地址
    protected $urlBase = "https://api.mch.weixin.qq.com/API_URL?ACCESS_TOKEN";

    /**
     * 生成支付签名
     *
     * @param array  $data     参与签名的数据
     * @param string $signType 参与签名的类型
     * @param string $buff     参与签名字符串前缀
     *
     * @return string
     */
    public function getPaySign(array $data, string $signType = 'MD5', string $buff = '')
    {
        ksort($data);
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        foreach ($data as $k => $v) {
            if ('' === $v || null === $v) {
                continue;
            }
            $buff .= "{$k}={$v}&";
        }
        $buff .= ("key=" . $this->config->get('mch_key'));
        if (strtoupper($signType) === 'MD5') {
            return strtoupper(md5($buff));
        }
        return strtoupper(hash_hmac('SHA256', $buff, $this->config->get('mch_key')));
    }

    /**
     * 数组转XML内容
     *
     * @param array $data
     *
     * @return string
     */
    public function arr2xml(array $data)
    {
        return "<xml>" . $this->arr2xmlAchieve($data) . "</xml>";
    }


    /**
     * XML内容生成
     *
     * @param array  $data 数据
     * @param string $content
     *
     * @return string
     */
    private function arr2xmlAchieve(array $data, string $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = 'item';
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= $this->arr2xmlAchieve($val);
            } elseif (is_string($val)) {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            } else {
                $content .= $val;
            }
            $content .= "</{$key}>";
        }
        return $content;
    }
}