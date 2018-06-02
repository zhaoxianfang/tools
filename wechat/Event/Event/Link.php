<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Link extends Event
{
    public function isValid()
    {
        return 'link' === $this['MsgType'];
    }
}
