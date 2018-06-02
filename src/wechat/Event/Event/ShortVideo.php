<?php

namespace zxf\Wechat\Event\Event;

use zxf\Wechat\Event\Event;

class ShortVideo extends Event
{
    public function isValid()
    {
        return 'shortvideo' === $this['MsgType'];
    }
}
