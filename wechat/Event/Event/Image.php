<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Image extends Event
{
    public function isValid()
    {
        return 'image' === $this['MsgType'];
    }
}
