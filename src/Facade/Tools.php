<?php

namespace zxf\Facade;

class Tools extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\tools\Tools::class;
    }
}