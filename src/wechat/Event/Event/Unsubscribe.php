<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Unsubscribe extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('unsubscribe' === $this['Event']);
    }
}
