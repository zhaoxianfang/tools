<?php

namespace zxf\Facade;

class Tools extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Tools\Tools::class;
    }
}