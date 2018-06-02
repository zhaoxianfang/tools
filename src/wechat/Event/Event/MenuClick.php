<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class MenuClick extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('CLICK' === $this['Event']);
    }
}
