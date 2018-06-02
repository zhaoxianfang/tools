<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class Location extends Event
{
    public function isValid()
    {
        return 'location' === $this['MsgType'];
    }
}
