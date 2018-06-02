<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class Location extends Event
{
    public function isValid()
    {
        return 'location' === $this['MsgType'];
    }
}
