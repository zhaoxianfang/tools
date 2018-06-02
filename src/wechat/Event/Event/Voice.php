<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Voice extends Event
{
    public function isValid()
    {
        return 'voice' === $this['MsgType'];
    }
}
