<?php

namespace zxf\Pay;

use Exception;

/**
 * 支付模块
 */
class PayFactory
{
    // 支付渠道
    const PAY_DRIVER_WECHAT = 'Wechat';
    const PAY_DRIVER_ALIPAY = 'Ali';

    public static $payDriverMap = [
        self::PAY_DRIVER_WECHAT => '微信支付',
        self::PAY_DRIVER_ALIPAY => '支付宝支付',
    ];

    private static $bindingDrivers = [
        self::PAY_DRIVER_WECHAT => WechatPayFactory::class,
        self::PAY_DRIVER_ALIPAY => AliPayFactory::class,
    ];

    /**
     * 获取某个支付方式的接口实现
     * 如果在自动测试中默认返回模拟实现
     *
     * @param       $driver
     * @param array $config
     *
     * @return mixed
     * @throws Exception
     */
    public static function call($driver, array $config = [])
    {
        if (in_array($driver, array_keys(self::$payDriverMap))) {
            return new self::$bindingDrivers[$driver]($config);
        } else {
            throw new Exception('支付方式不存在:' . $driver);
        }
    }

    /**
     * 动态的获取一个接口实现
     *
     * @param string $driver
     * @param array  $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic(string $driver, array $arguments)
    {
        return self::call($driver, ...$arguments);
    }
}