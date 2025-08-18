<?php

namespace zxf\Facade;

use Exception;

/**
 * 门面基础类，用于获取目标类
 */
class FacadeBase
{
    public static function __callStatic($method, $parameters)
    {
        $facadeClass = get_called_class(); // Facade 门面类
        if (! is_subclass_of($facadeClass, FacadeInterface::class)) {
            throw new Exception("The class [{$facadeClass}] must be a type of ".FacadeInterface::class.'.');
        }
        $targetClass = $facadeClass::getFacadeAccessor(); // 实际作用的目标类
        if (method_exists($targetClass, 'instance')) {
            if ($method == 'instance') {
                return $targetClass::instance();
            } else {
                return $targetClass::instance()->$method(...$parameters);
            }

        } else {
            return (new $targetClass)->$method(...$parameters);
        }

    }

    /**
     * @throws Exception
     */
    public function __call($method, $parameters)
    {
        $facadeClass = get_class(); // Facade 门面类
        if (! is_subclass_of($facadeClass, FacadeInterface::class)) {
            throw new Exception("The class [{$facadeClass}] must be a type of ".FacadeInterface::class.'.');
        }
        $targetClass = $facadeClass::getFacadeAccessor(); // 实际作用的目标类
        if (method_exists($targetClass, $method)) {
            return $targetClass::$method(...$parameters);
        } else {
            throw new Exception($method.' is not a function.');
        }
    }
}
