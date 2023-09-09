<?php

namespace zxf\Pay;

use Exception;
use zxf\Pay\Contracts\PayInterface;
use zxf\Pay\WeChat\V3\App;
use zxf\Pay\WeChat\V3\H5;
use zxf\Pay\WeChat\V3\JsApi;
use zxf\Pay\WeChat\V3\MiniProgram;
use zxf\Pay\WeChat\V3\Native;

/**
 * 微信支付工厂类
 *
 * WechatPayFactory::JsApi(?array $config)->preOrder();
 * WechatPayFactory::App(?array $config)->preOrder();
 * WechatPayFactory::H5(?array $config)->preOrder();
 * WechatPayFactory::Native(?array $config)->preOrder();
 * WechatPayFactory::MiniProgram(?array $config)->preOrder();
 */
class WechatPayFactory
{
    // 支付方式
    const PAY_TYPE_JSAPI        = 'JsApi';
    const PAY_TYPE_APP          = 'App';
    const PAY_TYPE_H5           = 'H5';
    const PAY_TYPE_NATIVE       = 'Native';
    const PAY_TYPE_MINI_PROGRAM = 'MiniProgram';

    public static array $payTypeMap = [
        self::PAY_TYPE_JSAPI        => 'JSAPI支付',
        self::PAY_TYPE_APP          => 'APP支付',
        self::PAY_TYPE_H5           => 'H5支付',
        self::PAY_TYPE_NATIVE       => 'Native支付',
        self::PAY_TYPE_MINI_PROGRAM => '小程序支付',
    ];

    // 支付方式
    private static array $bindingTypes = [
        self::PAY_TYPE_JSAPI        => JsApi::class,
        self::PAY_TYPE_APP          => App::class,
        self::PAY_TYPE_H5           => H5::class,
        self::PAY_TYPE_NATIVE       => Native::class,
        self::PAY_TYPE_MINI_PROGRAM => MiniProgram::class,
    ];

    /**
     * 调用微信支付的各种支持方式
     *
     * @param string     $driver
     * @param array|null $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic(string $driver = 'default', ?array $arguments = []): PayInterface
    {
        if (in_array($driver, array_keys(self::$payTypeMap))) {
            return new self::$bindingTypes[$driver](...$arguments);
        } else {
            throw new Exception('支付方式不存在:' . $driver);
        }
    }
}