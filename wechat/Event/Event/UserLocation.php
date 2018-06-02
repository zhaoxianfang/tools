<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class UserLocation extends Event
{
    public function isValid()
    {
        return ('event' === $this['MsgType'])
            && ('LOCATION' === $this['Event']);
    }
}
