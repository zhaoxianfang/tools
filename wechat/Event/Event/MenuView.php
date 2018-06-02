<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class MenuView extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('VIEW' === $this['Event']);
    }
}
