<?php

namespace zxf\WeChat\Offiaccount;

use zxf\WeChat\WeChatBase;

// 获取微信服务器IP ok
class Ip extends WeChatBase
{
    //获取微信API接口 IP地址
    public function domainIps()
    {
        return $this->get("cgi-bin/get_api_domain_ip");
    }

    //获取微信callback IP地址
    public function callbackIps()
    {
        return $this->get("cgi-bin/getcallbackip");
    }
}