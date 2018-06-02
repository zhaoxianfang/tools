<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Link extends Event
{
    public function isValid()
    {
        return 'link' === $this['MsgType'];
    }
}
