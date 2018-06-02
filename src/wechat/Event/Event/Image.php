<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Image extends Event
{
    public function isValid()
    {
        return 'image' === $this['MsgType'];
    }
}
