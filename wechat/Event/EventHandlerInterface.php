<?php

namespace zxf\tool\Wechat\Event;

use Symfony\Component\HttpFoundation\Request;

interface EventHandlerInterface
{
    /**
     * handle event via request.
     */
    public function handle(EventListenerInterface $listener);
}
