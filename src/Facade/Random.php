<?php

namespace zxf\Facade;

class Random extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Tools\Random::class;
    }
}