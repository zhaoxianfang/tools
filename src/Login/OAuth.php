<?php

/**
 * 第三方登陆实例抽象类
 */

namespace zxf\Login;

use Exception;
use zxf\Login\Contracts\GatewayInterface;
use zxf\Login\Helper\Str;

/**
 * @method static Gateways\Qq Qq(string|array|null $config = []) QQ
 * @method static Gateways\Sina Sina(string|array|null $config = []) Sina
 * @method static Gateways\Wechat Wechat(string|array|null $config = []) 微信开放平台登录
 * @method static Gateways\Alipay Alipay(string|array|null $config = []) 阿里云
 * @method static Gateways\Facebook Facebook(string|array|null $config = []) Facebook
 * @method static Gateways\Github Github(string|array|null $config = []) Github
 * @method static Gateways\Google Google(string|array|null $config = []) Google
 * @method static Gateways\Line Line(string|array|null $config = []) Line
 * @method static Gateways\Twitter Twitter(string|array|null $config = []) Twitter
 * @method static Gateways\Douyin Douyin(string|array|null $config = []) 抖音
 */
abstract class OAuth
{
    /**
     * Description:  init
     *
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected static function init($gateway, string|array|null $config = [])
    {
        $gateway = Str::uFirst($gateway);
        $class = __NAMESPACE__.'\\Gateways\\'.$gateway;
        if (class_exists($class)) {
            $app = new $class($config);
            if ($app instanceof GatewayInterface) {
                return $app;
            }
            throw new Exception("第三方登录基类 [$gateway] 必须继承抽象类 [GatewayInterface]");
        }
        throw new Exception("第三方登录基类 [$gateway] 不存在");
    }

    /**
     * Description:  __callStatic
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function __callStatic($gateway, mixed $config)
    {
        return self::init($gateway, ...$config);
    }
}
