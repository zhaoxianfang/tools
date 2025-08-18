<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序即时配送 - 运力方
 */
class ImmediateDelivery extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 更新配送单状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/immediate-delivery/deliver-by-provider/updateOrder.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function updateOrder(array $data)
    {
        return $this->post('cgi-bin/express/local/delivery/update_order', $data);
    }
}
