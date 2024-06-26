<?php

namespace zxf\WeChat\OfficialAccount;

use zxf\WeChat\Contracts\WeChatBase;


/**
 * 微信发票
 *
 * @link https://developers.weixin.qq.com/doc/offiaccount/WeChat_Invoice/E_Invoice/Vendor_API_List.html
 */
class Invoice extends WeChatBase
{
    public bool $useToken = true;

}