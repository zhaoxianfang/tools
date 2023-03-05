<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序物流助手
 * Class Logistics
 *
 * @package WeMini
 */
class Logistics extends WeChatBase
{
    /**
     * 生成运单
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addOrder($data)
    {
        return $this->post("cgi-bin/express/business/order/add", $data);
    }

    /**
     * 取消运单
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function cancelOrder($data)
    {
        return $this->post("cgi-bin/express/business/order/cancel", $data);
    }

    /**
     * 获取支持的快递公司列表
     *
     * @return array
     * @throws Exception
     */
    public function getAllDelivery()
    {
        return $this->get("cgi-bin/express/business/delivery/getall");
    }

    /**
     * 获取运单数据
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getOrder($data)
    {
        return $this->post("cgi-bin/express/business/order/get", $data);
    }

    /**
     * 查询运单轨迹
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getPath($data)
    {
        return $this->post("cgi-bin/express/business/path/get", $data);
    }

    /**
     * 获取打印员。若需要使用微信打单 PC 软件，才需要调用
     *
     * @return array
     * @throws Exception
     */
    public function getPrinter()
    {
        return $this->get("cgi-bin/express/business/printer/getall");
    }

    /**
     * 获取电子面单余额。仅在使用加盟类快递公司时，才可以调用
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getQuota($data)
    {
        return $this->post("cgi-bin/express/business/path/get", $data);
    }

    /**
     * 模拟快递公司更新订单状态, 该接口只能用户测试
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function testUpdateOrder($data)
    {
        return $this->post("cgi-bin/express/business/test_update_order", $data);
    }

    /**
     * 配置面单打印员，若需要使用微信打单 PC 软件，才需要调用
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePrinter($data)
    {
        return $this->post("cgi-bin/express/business/printer/update", $data);
    }

    /**
     * 获取面单联系人信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getContact($data)
    {
        return $this->post("cgi-bin/express/delivery/contact/get", $data);
    }

    /**
     * 预览面单模板。用于调试面单模板使用
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function previewTemplate($data)
    {
        return $this->post("cgi-bin/express/delivery/template/preview", $data);
    }

    /**
     * 更新商户审核结果
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateBusiness($data)
    {
        return $this->post("cgi-bin/express/delivery/service/business/update", $data);
    }

    /**
     * 更新运单轨迹
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePath($data)
    {
        return $this->post("cgi-bin/express/delivery/path/update", $data);
    }
}