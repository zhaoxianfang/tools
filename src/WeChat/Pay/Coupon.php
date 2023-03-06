<?php


namespace zxf\WeChat\Pay;

use Exception;
use zxf\WeChat\Contracts\BasicWePay;

/**
 * 微信商户代金券
 */
class Coupon extends BasicWePay
{
    /**
     * 发放代金券
     *
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    public function create(array $options)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/send_coupon";
        return $this->callPostApi($url, $options, true, "MD5");
    }

    /**
     * 查询代金券批次
     *
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    public function queryStock(array $options)
    {
        return $this->post("mmpaymkttransfers/query_coupon_stock", $options);
    }

    /**
     * 查询代金券信息
     *
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    public function queryInfo(array $options)
    {
        return $this->post("mmpaymkttransfers/query_coupon_stock", $options);
    }

}