<?php

namespace zxf\Pay\WeChat\V3;

use zxf\Pay\Contracts\PayInterface;
use zxf\Pay\Contracts\TraitWechatPayV3Interface;
use zxf\Pay\Traits\WechatPayV3Trait;
use zxf\Pay\WeChat\WeChatPayBase;

class App extends WeChatPayBase implements TraitWechatPayV3Interface, PayInterface
{
    use WechatPayV3Trait;

    protected $driverName = 'app';// jsapi,app,h5,native // JSAPI 和小程序使用 的都是 jsapi

    /**
     * 发起支付
     * (获取APP支付参数)
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_2_4.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_2_4.shtml
     */
    public function pay(?string $prepay_id = '')
    {
        return $this->getAppSignParams($prepay_id);
    }

}