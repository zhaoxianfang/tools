<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 物流助手
 */
class Express extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 绑定/解绑物流账号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/bindAccount.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function bindAccount($data)
    {
        return $this->post('cgi-bin/express/business/account/bind', $data);
    }

    /**
     * 获取所有绑定的物流账号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getAllAccount.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getAllAccount($data)
    {
        return $this->get('cgi-bin/express/business/account/getall', $data);
    }

    /**
     * 获取支持的快递公司列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getAllDelivery.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getAllDelivery($data)
    {
        return $this->get('cgi-bin/express/business/delivery/getall', $data);
    }

    /**
     * 取消运单
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/cancelOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function cancelOrder($data)
    {
        return $this->post('cgi-bin/express/business/order/cancel', $data);
    }

    /**
     * 配置面单打印员
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/updatePrinter.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePrinter($data)
    {
        return $this->post('cgi-bin/express/business/printer/update', $data);
    }

    /**
     * 获取电子面单余额
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getQuota.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getQuota($data)
    {
        return $this->post('cgi-bin/express/business/quota/get', $data);
    }

    /**
     * 获取运单数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getOrder($data)
    {
        return $this->post('cgi-bin/express/business/order/get', $data);
    }

    /**
     * 模拟更新订单状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/testUpdateOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function testUpdateOrder($data)
    {
        return $this->post('cgi-bin/express/business/test_update_order', $data);
    }

    /**
     * 获取打印员
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getPrinter.html
     *
     * @return array
     * @throws Exception
     */
    public function getPrinter()
    {
        return $this->get('cgi-bin/express/business/printer/getall');
    }


    /**
     * 查询运单轨迹
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/getPath.html
     *
     * @param string      $delivery_id 快递公司ID，参见getAllDelivery
     * @param string      $waybill_id  运单ID
     *
     * @param string|null $openid      用户openid，当add_source=2时无需填写（不发送物流服务通知）
     *
     * @return array
     * @throws Exception
     */
    public function getPath(string $delivery_id, string $waybill_id, ?string $openid)
    {
        return $this->post('cgi-bin/express/business/path/get', [
            'openid'      => $openid,
            'delivery_id' => $delivery_id,
            'waybill_id'  => $waybill_id,
        ]);
    }

    /**
     * 批量获取运单数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/batchGetOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function batchGetOrder(array $data)
    {
        return $this->post('cgi-bin/express/business/order/batchget', $data);
    }

    /**
     * 生成运单
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-business/addOrder.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addOrder(array $data)
    {
        return $this->post('cgi-bin/express/business/order/add', $data);
    }

    /**
     * 更新商户审核结果
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-provider/updateBusiness.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateBusiness(array $data)
    {
        return $this->post('cgi-bin/express/delivery/service/business/update', $data);
    }

    /**
     * 更新运单轨迹
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-provider/updatePath.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePath(array $data)
    {
        return $this->post('cgi-bin/express/delivery/path/update', $data);
    }

    /**
     * 预览面单模板
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-provider/previewTemplate.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function previewTemplate(array $data)
    {
        return $this->post('cgi-bin/express/delivery/template/preview', $data);
    }

    /**
     * 获取面单联系人信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/express/express-by-provider/getContact.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getContact(array $data)
    {
        return $this->post('cgi-bin/express/delivery/contact/get', $data);
    }

}