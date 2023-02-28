<?php

namespace zxf\Facade;

class Xml extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\tools\Xml::class;
    }
}