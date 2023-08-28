<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 购物助手
 */
class ShoppingOrder extends WeChatBase
{
    public $useToken = false;

    /**
     * 上传购物详情
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/shopping-order/normal-shopping-detail/uploadShoppingInfo.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function uploadShoppingInfo(array $data)
    {
        return $this->post('shop/register', $data);
    }

    /**
     * 上传物流信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/shopping-order/normal-shopping-detail/uploadShippingInfo.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function uploadShippingInfo(array $data)
    {
        return $this->post('user-order/orders/shippings', $data);
    }

    /**
     * 上传合单购物详情
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/shopping-order/shopping-detail/uploadCombinedShoppingInfo.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function uploadCombinedShoppingInfo(array $data)
    {
        return $this->post('user-order/combine-orders', $data);
    }

    /**
     * 上传合单物流信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/shopping-order/shopping-detail/uploadCombinedShippingInfo.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function uploadCombinedShippingInfo(array $data)
    {
        return $this->post('user-order/combine-orders/shippings', $data);
    }

    /**
     * 验证购物订单上传结果
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/shopping-order/upload-result/ShoppingInfoVerifyUploadResult.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function ShoppingInfoVerifyUploadResult(array $data)
    {
        return $this->post('user-order/shoppinginfo/verify', $data);
    }
}