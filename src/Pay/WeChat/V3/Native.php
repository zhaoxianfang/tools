<?php

namespace zxf\Pay\WeChat\V3;

use zxf\Pay\Contracts\PayInterface;
use zxf\Pay\Contracts\TraitWechatPayV3Interface;
use zxf\Pay\Traits\WechatPayV3Trait;
use zxf\Pay\WeChat\WeChatPayBase;

class Native extends WeChatPayBase implements TraitWechatPayV3Interface, PayInterface
{
    use WechatPayV3Trait;

    protected $driverName = 'native';// jsapi,app,h5,native // JSAPI 和小程序使用 的都是 jsapi


    /**
     * 发起支付 商户后台系统先调用微信支付的Native下单接口，微信后台系统返回链接参数code_url，商户后台系统将code_url值生成二维码图片，用户使用微信客户端扫码后发起支付。
     * (获取Native支付参数)
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_4_4.shtml
     */
    public function pay(array $data = [])
    {
        return $this->preOrder($data);
    }

}