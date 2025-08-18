<?php

namespace zxf\WeChat;

use zxf\WeChat\Contracts\WeChatBase;
use zxf\WeChat\Contracts\WechatPushEvent;
use zxf\WeChat\Contracts\WeChatWorkBase;

class WechatFactory extends WeChatBase
{
    // 是否在构造函数中初始化配置
    protected bool $defaultInit = false;

    // 使用场景:微信小程序、微信开放平台、微信公众号等
    public function defaultScene(string $driver = 'default'): object
    {
        return parent::instance($driver);
    }

    // 使用场景:企业微信
    public function workScene(string $driver = 'work'): object
    {
        return new WeChatWorkBase($driver); // 企业微信
    }

    // 使用场景: 服务端接收消息
    public function receiveScene(string $driver = 'default'): object
    {
        return new WechatPushEvent($driver); // 服务端接收消息
    }
}
