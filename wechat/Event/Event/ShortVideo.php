<?php

namespace zxf\tool\Wechat\Event\Event;

use zxf\tool\Wechat\Event\Event;

class ShortVideo extends Event
{
    public function isValid()
    {
        return 'shortvideo' === $this['MsgType'];
    }
}
