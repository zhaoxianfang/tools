<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Subscribe extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('subscribe' === $this['Event'])
            && empty($this['EventKey']);
    }
}
