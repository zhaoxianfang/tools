<?php

namespace zxf\Pay\WeChat\PayScore;

use Exception;
use zxf\Pay\WeChat\WeChatPayBase;

/**
 * 微信支付分停车
 */
class Vehicle extends WeChatPayBase
{
    /**
     * 创建停车入场
     *
     * @link https://pay.weixin.qq.com/docs/partner/apis/wexin-pay-score-parking/parkings/create-parking.html
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function createParking(array $data = [])
    {
        $url = "v3/vehicle/parking/parkings";
        return $this->url($url)->post($data);
    }

    /**
     * 扣费受理-查询订单
     *
     * @link https://pay.weixin.qq.com/docs/partner/apis/wexin-pay-score-parking/transactions/query-transaction.html
     *
     * @param string $out_trade_no 【商户订单号】 商户系统内部订单号，只能是数字、大小写字母，且在同一个商户号下唯一
     *
     * @return mixed
     */
    public function queryTransaction(string $out_trade_no = '')
    {
        $url = "v3/vehicle/transactions/out-trade-no/{$out_trade_no}";
        $this->withRequestFields($this->isService() ? ['sp_mchid'] : []);
        return $this->url($url)->get();
    }
}