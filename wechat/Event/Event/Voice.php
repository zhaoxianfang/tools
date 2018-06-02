<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Voice extends Event
{
    public function isValid()
    {
        return 'voice' === $this['MsgType'];
    }
}
