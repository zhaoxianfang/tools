<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序即时配送
 * Class Delivery
 * @package WeMini
 */
class Delivery extends WeChatBase
{

    /**
     * 异常件退回商家商家确认收货接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function abnormalConfirm($data)
    {
        $url = 'cgi-bin/express/local/business/order/confirm_return';
        return $this->post($url, $data);
    }

    /**
     * 下配送单接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/add';
        return $this->post($url, $data);
    }

    /**
     * 可以对待接单状态的订单增加小费
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addTip($data)
    {
        $url = 'cgi-bin/express/local/business/order/addtips';
        return $this->post($url, $data);
    }

    /**
     * 取消配送单接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function cancelOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/cancel';
        return $this->post($url, $data);
    }

    /**
     * 获取已支持的配送公司列表接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getAllImmeDelivery($data)
    {
        $url = 'cgi-bin/express/local/business/delivery/getall';
        return $this->post($url, $data);
    }

    /**
     * 拉取已绑定账号
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getBindAccount($data)
    {
        $url = 'cgi-bin/express/local/business/shop/get';
        return $this->post($url, $data);
    }

    /**
     * 拉取配送单信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/get';
        return $this->post($url, $data);
    }

    /**
     * 模拟配送公司更新配送单状态
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function mockUpdateOrder($data)
    {
        $url = 'cgi-bin/express/local/business/test_update_order';
        return $this->post($url, $data);
    }

    /**
     * 预下配送单接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function preAddOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/pre_add';
        return $this->post($url, $data);
    }

    /**
     * 预取消配送单接口
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function preCancelOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/precancel';
        return $this->post($url, $data);
    }

    /**
     * 重新下单
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function reOrder($data)
    {
        $url = 'cgi-bin/express/local/business/order/readd';
        return $this->post($url, $data);
    }

}