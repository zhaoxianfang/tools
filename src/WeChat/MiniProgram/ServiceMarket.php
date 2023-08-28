<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信服务市场
 */
class ServiceMarket extends WeChatBase
{
    public $useToken = true;

    /**
     * 调用服务市场接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/wx-service-market/invokeService.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function invokeService($data)
    {
        return $this->post('wxa/servicemarket', $data);
    }
}