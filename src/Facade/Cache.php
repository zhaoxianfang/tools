<?php

namespace zxf\Facade;

class Cache extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\tools\Cache::class;
    }
}
