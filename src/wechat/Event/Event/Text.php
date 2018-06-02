<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Text extends Event
{
    public function isValid()
    {
        return 'text' === $this['MsgType'];
    }
}
