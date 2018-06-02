<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class MenuView extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('VIEW' === $this['Event']);
    }
}
