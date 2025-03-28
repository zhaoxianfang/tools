<?php

/**
 * 第三方登陆实例抽象类
 */

namespace zxf\Login;

use Exception;
use zxf\Login\Contracts\GatewayInterface;
use zxf\Login\Helper\Str;

/**
 * @method static Gateways\Qq Qq(?array $config = []) QQ
 * @method static Gateways\Sina Sina(?array $config = []) Sina
 * @method static Gateways\Wechat Wechat(?array $config = []) 微信开放平台登录
 * @method static Gateways\Alipay Alipay(?array $config=[]) 阿里云
 * @method static Gateways\Facebook Facebook(?array $config=[]) Facebook
 * @method static Gateways\Github Github(?array $config=[]) Github
 * @method static Gateways\Google Google(?array $config=[]) Google
 * @method static Gateways\Line Line(?array $config=[]) Line
 * @method static Gateways\Twitter Twitter(?array $config=[]) Twitter
 * @method static Gateways\Douyin Douyin(?array $config=[]) 抖音
 */
abstract class OAuth
{
    /**
     * Description:  init
     *
     * @param            $gateway
     * @param array|null $config
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected static function init($gateway, ?array $config = [])
    {
        if (empty($config)) {
            $name = strtolower($gateway);
            if (function_exists('config')) {
                $config = config("tools_oauth.{$name}.default") ?? [];
            }
            empty($config) && throw new Exception("第三方登录 [$gateway] config配置不能为空");
        }
        $baseConfig = [
            'app_id' => '',
            'app_secret' => '',
            'callback' => '',
            'scope' => '',
            'type' => '',
        ];
        $gateway = Str::uFirst($gateway);
        $class = __NAMESPACE__.'\\Gateways\\'.$gateway;
        if (class_exists($class)) {
            // $config 的值递归替换 $baseConfig的值
            $app = new $class(array_replace_recursive($baseConfig, $config));
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
