<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序即时配送
 */
class Delivery extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 异常件退回商家商家确认
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/abnormalConfirm.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function abnormalConfirm($data)
    {
        return $this->post('cgi-bin/express/local/business/order/confirm_return', $data);
    }

    /**
     * 下配送单接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/addLocalOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addOrder($data)
    {
        return $this->post('cgi-bin/express/local/business/order/add', $data);
    }

    /**
     * 可以对待接单状态的订单增加小费
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/addTips.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addTip($data)
    {
        return $this->post('cgi-bin/express/local/business/order/addtips', $data);
    }

    /**
     * 取消配送单接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/cancelLocalOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function cancelOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/business/order/cancel', $data);
    }

    /**
     * 获取已支持的配送公司列表接口
     *
     * @link  https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/getAllImmeDelivery.html
     *
     * @return array
     * @throws Exception
     */
    public function getAllImmeDelivery()
    {
        return $this->post('cgi-bin/express/local/business/delivery/getall');
    }

    /**
     * 拉取已绑定账号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/getBindAccount.html
     *
     * @return array
     * @throws Exception
     */
    public function getBindAccount()
    {
        return $this->post('cgi-bin/express/local/business/shop/get');
    }

    /**
     * 拉取配送单信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/getLocalOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/business/order/get', $data);
    }

    /**
     * 模拟配送公司更新配送单状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/mockUpdateOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function mockUpdateOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/business/test_update_order', $data);
    }

    /**
     * 预下配送单接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/preAddOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function preAddOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/business/order/pre_add', $data);
    }

    /**
     * 预取消配送单接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/preCancelOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function preCancelOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/business/order/precancel', $data);
    }

    /**
     * 申请开通即时配送
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/openDelivery.html
     *
     * @return array
     * @throws Exception
     */
    public function openDelivery()
    {
        return $this->post('cgi-bin/express/local/business/open');
    }

    /**
     * 发起绑定请求
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/bindLocalAccount.html
     *
     * @param string $delivery_id 配送公司ID
     *
     * @return array
     * @throws Exception
     */
    public function bindLocalAccount(string $delivery_id)
    {
        return $this->post('cgi-bin/express/local/business/shop/add', ['delivery_id' => $delivery_id]);
    }

    /**
     * 重新下单
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/reOrder.html
     *
     * @param array $date
     *
     * @return array
     * @throws Exception
     */
    public function reOrder(array $date)
    {
        return $this->post('cgi-bin/express/local/business/order/readd', $date);
    }

    /**
     * 模拟更新配送单状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-business/realMockUpdateOrder.html
     *
     * @param array $date
     *
     * @return array
     * @throws Exception
     */
    public function realMockUpdateOrder(array $date)
    {
        return $this->post('cgi-bin/express/local/business/realmock_update_order', $date);
    }

}