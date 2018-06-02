<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Subscribe extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('subscribe' === $this['Event'])
            && empty($this['EventKey']);
    }
}
