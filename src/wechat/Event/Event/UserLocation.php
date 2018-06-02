<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class UserLocation extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('LOCATION' === $this['Event']);
    }
}
