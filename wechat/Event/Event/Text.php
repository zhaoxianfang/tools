<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Text extends Event
{
    public function isValid()
    {
        return 'text' === $this['MsgType'];
    }
}
