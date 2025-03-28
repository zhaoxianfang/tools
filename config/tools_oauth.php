<?php

use zxf\Login\Constants\ConstCode;

/**
 * 第三方授权登录配置
 */
return [
    // 腾讯QQ
    'qq'       => [
        'default' => [
            'app_id'     => env('OAUTH_QQ_APP_ID', ''),
            'app_secret' => env('OAUTH_QQ_APP_SECRET', ''),
            'callback'   => env('OAUTH_QQ_CALLBACK', ''),
            'scope'      => 'get_user_info',
            'is_unioid'  => true, // 是否已申请 开通unioid
        ],
        'mobile'  => [
        ],
    ],
    // 新浪微博
    'sina'     => [
        'default' => [
            'app_id'     => env('OAUTH_SINA_APP_ID', ''),
            'app_secret' => env('OAUTH_SINA_APP_SECRET', ''),
            'callback'   => env('OAUTH_SINA_CALLBACK', ''),
            'scope'      => 'all',
        ],
    ],
    // 微信
    'wechat'   => [
        // PC 扫码登录【需要开通微信开放平台应用 open.weixin.qq.com】
        'default' => [
            'app_id'     => env('OAUTH_WECHAT_APP_ID', ''),
            'app_secret' => env('OAUTH_WECHAT_APP_SECRET', ''),
            'callback'   => env('OAUTH_WECHAT_CALLBACK', ''),
            'scope'      => 'snsapi_login', // PC扫码登录
            // 'proxy_url' => '',//如果不需要代理请注释此行
            // 'proxy_url' => '',//如果不需要代理请注释此行
        ],
        // 移动端登录
        'mobile'  => [
            'app_id'     => '',
            'app_secret' => '',
            'callback'   => 'https://example.com/app/wechat',
            // snsapi_base: 静默授权; snsapi_userinfo: 获取用户信息
            'scope'      => 'snsapi_userinfo',
            // 'proxy_url' => '',//如果不需要代理请注释此行
            // 'proxy_url' => '',//如果不需要代理请注释此行
        ],
        // app 登录
        'app'     => [
            'app_id'     => '',
            'app_secret' => '',
            'type'       => 'app', // 登录类型app
        ],
        /**
         * 微信小程序只能获取到 openid session_key
         * 详见文档 https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
         */
        'applets' => [
            'app_id'     => '',
            'app_secret' => '',
            'type'       => 'applets', // 登录类型小程序
        ],

        /**
         * 如果需要微信代理登录(微信app内登录)，则需要：
         * 1.将 example/wx_proxy.php 放置在微信公众号设定的回调域名某个地址，如 https://www.example.com/proxy/wx_proxy.php
         * 2.config中加入配置参数proxy_url，地址为 https://www.example.com/proxy/wx_proxy.php
         * 如下所示:
         *    'proxy_url' = 'http://www.example.com/proxy/wx_proxy.php';
         *
         */
    ],
    // GitHub
    'github'   => [
        'default' => [
            'application_name' => '',
            'app_id'           => '',
            'app_secret'       => '',
            'callback'         => 'https://example.com/app/github',
        ],
    ],
    // 支付宝
    'alipay'   => [
        'default' => [
            'app_id'      => '',
            'scope'       => 'auth_user',
            'aes'         => '', // AES密钥
            'callback'    => 'https://example.com/app/alipay',
            'pem_private' => '/your/cert/path/rsaPrivateKey.pem', // 你的私钥
            'pem_public'  => '/your/cert/path/alipayrsaPublicKey.pem', // 支付宝公钥
            'is_sandbox'  => false,   // 是否是沙箱环境
        ],
    ],
    // Facebook 脸书
    'facebook' => [
        'default' => [
            'app_id'     => '',
            'app_secret' => '',
            // public_profile,user_gender: user_gender需要审核，所以不一定能获取到
            'scope'      => 'public_profile,user_gender', // user_gender需要审核，所以不一定能获取到
            // facebook有个特殊的配置$config['field']，
            // 默认是'id,name,gender,picture.width(400)'，你可以根据需求参考官方文档自行选择要获取的用户信息
        ],
    ],
    'twitter'  => [
        'default' => [
            'app_id'     => '',
            'app_secret' => '',
        ],
    ],
    'line'     => [
        'default' => [
            'app_id'     => '',
            'app_secret' => '',
            'scope'      => 'profile',
        ],
    ],
    'naver'    => [
        'default' => [
            'app_id'     => '',
            'app_secret' => '',
            'callback'   => 'https://example.com/app/naver',
        ],
    ],
    'google'   => [
        'default' => [
            'app_id'     => '***.apps.googleusercontent.com',
            'app_secret' => '',
            'scope'      => 'https://www.googleapis.com/auth/userinfo.profile',
            'callback'   => 'https://example.com/app/google',
        ],
    ],
    'douyin'   => [
        // 抖音官方：请确保授权回调域网站协议为 https
        // pc
        'default' => [
            'oauth_type'    => ConstCode::TYPE_DOUYIN, // 抖音douyin，头条toutiao，西瓜xigua，使用\tinymeng\OAuth2\Helper\ConstCode
            'app_id'        => '',
            'app_secret'    => '',
            'callback'      => 'https://example.com/app/douyin',
            'scope'         => 'trial.whitelist,user_info', // trial.whitelist为白名单人员权限,上线后删掉
            'optionalScope' => '', // 应用授权可选作用域,多个授权作用域以英文逗号（,）分隔，每一个授权作用域后需要加上一个是否默认勾选的参数，1为默认勾选，0为默认不勾选
        ],
        'mobile'  => [
            // 待完善TODO...
            'app_id'     => '',
            'app_secret' => '',
            'callback'   => 'https://example.com/app/douyin',
            'scope'      => 'login_id', // login_id为静默授权
        ],
        'app'     => [
            // 待完善TODO...
        ],
        'applets' => [
            // 待完善TODO...
            'app_id'     => '',
            'app_secret' => '',
        ],
    ],
    // 阿里云
    'aliyun'   => [
        'default' => [
            'app_id'     => '',
            'app_secret' => '',
            'scope'      => 'openid aliuid profile',
            'callback'   => 'https://example.com/app/aliyun',
        ],
    ],
];