<?php

namespace zxf\Facade;

use zxf\WeChat\WechatFactory;

class Wechat extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return WechatFactory::class;
    }
}
