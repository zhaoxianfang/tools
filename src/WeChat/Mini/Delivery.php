<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序即时配送
 * Class Delivery
 *
 * @package WeMini
 */
class Delivery extends WeChatBase
{

    /**
     * 异常件退回商家商家确认收货接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function abnormalConfirm($data)
    {
        return $this->post("cgi-bin/express/local/business/order/confirm_return", $data);
    }

    /**
     * 下配送单接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/add", $data);
    }

    /**
     * 可以对待接单状态的订单增加小费
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addTip($data)
    {
        return $this->post("cgi-bin/express/local/business/order/addtips", $data);
    }

    /**
     * 取消配送单接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function cancelOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/cancel", $data);
    }

    /**
     * 获取已支持的配送公司列表接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getAllImmeDelivery($data)
    {
        return $this->post("cgi-bin/express/local/business/delivery/getall", $data);
    }

    /**
     * 拉取已绑定账号
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getBindAccount($data)
    {
        return $this->post("cgi-bin/express/local/business/shop/get", $data);
    }

    /**
     * 拉取配送单信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/get", $data);
    }

    /**
     * 模拟配送公司更新配送单状态
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function mockUpdateOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/test_update_order", $data);
    }

    /**
     * 预下配送单接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function preAddOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/pre_add", $data);
    }

    /**
     * 预取消配送单接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function preCancelOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/precancel", $data);
    }

    /**
     * 重新下单
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function reOrder($data)
    {
        return $this->post("cgi-bin/express/local/business/order/readd", $data);
    }

}