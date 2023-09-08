<?php

namespace zxf\Pay\WeChat\V3;

use zxf\Pay\Contracts\PayInterface;
use zxf\Pay\Contracts\TraitWechatPayV3Interface;
use zxf\Pay\Traits\WechatPayV3Trait;
use zxf\Pay\WeChat\WeChatPayBase;

class H5 extends WeChatPayBase implements TraitWechatPayV3Interface, PayInterface
{
    use WechatPayV3Trait;

    protected $driverName = 'h5';// jsapi,app,h5,native // JSAPI 和小程序使用 的都是 jsapi


    /**
     * 发起支付  商户后台系统先调用微信支付的H5下单接口，微信后台系统返回链接参数h5_url，用户使用微信外部的浏览器访问该h5_url地址调起微信支付中间页
     * (获取H5支付参数)
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_3_4.shtml
     */
    public function pay(array $data = [])
    {
        return $this->preOrder($data);
    }

}