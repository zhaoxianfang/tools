<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Unsubscribe extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('unsubscribe' === $this['Event']);
    }
}
