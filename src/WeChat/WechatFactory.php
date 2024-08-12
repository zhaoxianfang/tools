<?php

namespace zxf\WeChat;


use zxf\WeChat\Contracts\WeChatBase;
use zxf\WeChat\Contracts\WechatPushEvent;
use zxf\WeChat\Contracts\WeChatWorkBase;

class WechatFactory extends WeChatBase
{

    public function __construct(string $driver = '')
    {
        // 什么也不做
    }

    public function defaultScene(string $driver = 'default'): object
    {
        $this->setDriver($driver);
        return $this; // 微信小程序、微信开放平台、微信公众号等
    }

    // 使用场景:企业微信
    public function workScene(string $driver = 'work'): object
    {
        return new WeChatWorkBase($driver); // 企业微信
    }

    // 使用场景: 服务端接收消息
    public function receiveScene(): object
    {
        return new WechatPushEvent(); // 服务端接收消息
    }
}
