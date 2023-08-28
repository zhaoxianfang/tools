<?php

namespace zxf\WeChat\Pay;

use Exception;
use zxf\WeChat\Contracts\WechatPay;

/**
 * 微信商户账单
 */
class Bill extends WechatPay
{
    public $useToken = false;

    /**
     * 下载对账单
     * TODO 待完善
     *
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_6
     *
     * @param array $options 静音参数
     *
     * @return bool|string
     * @throws Exception
     */
    public function downloadBill(array $options)
    {
        $params = array_merge($options, ['sign_type' => 'MD5']);

        $params['sign'] = $this->getPaySign($params, 'MD5');

        return $this->post('pay/downloadbill', $this->arr2xml($params));
    }


}