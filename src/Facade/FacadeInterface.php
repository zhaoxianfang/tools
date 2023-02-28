<?php

namespace zxf\Facade;

/**
 * 门面继承类，所有使用门面的类都应该继承此类
 */
interface FacadeInterface
{
    public static function getFacadeAccessor();
}