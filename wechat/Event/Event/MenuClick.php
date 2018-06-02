<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class MenuClick extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('CLICK' === $this['Event']);
    }
}
