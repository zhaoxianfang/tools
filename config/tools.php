<?php
/**
 * tools 配置参数
 */
return [
    //微博Web
    'sina'      => [
        'default' => [
            'wb_akey'         => env('TOOLS_SINA_WEB_AKEY', ''),
            'wb_skey'         => env('TOOLS_SINA_WEB_SKEY', ''),
            'wb_callback_url' => env('TOOLS_SINA_WEB_CALLBACK_URL', ''), //回调
        ],
    ],
    //QQ
    'qq'        => [
        'web'    => [
            'app_id'       => env('TOOLS_QQ_WEB_APP_ID', ''),
            'app_key'      => env('TOOLS_QQ_WEB_APP_KEY', ''),
            'callback_url' => env('TOOLS_QQ_WEB_CALLBACK_URL', ''),
        ],
        'mobile' => [
            'app_id'  => env('TOOLS_QQ_MOBILE_APP_ID', ''),
            'app_key' => env('TOOLS_QQ_MOBILE_APP_KEY', ''),
        ],
    ],
    //微信
    'wechat'    => [
        // 公众号
        'official_account' => [
            'default' => [
                'token'          => env('TOOLS_WECHAT_OFFICIAL_TOKEN', ''), //填写你设定的key
                'aes_key'        => env('TOOLS_WECHAT_OFFICIAL_AES_KEY', ''), //填写加密用的EncodingAESKey
                'app_id'         => env('TOOLS_WECHAT_OFFICIAL_APP_ID', ''), //填写高级调用功能的app id
                'app_secret'     => env('TOOLS_WECHAT_OFFICIAL_APP_SECRET', ''), //填写高级调用功能的密钥
                'token_callback' => env('TOOLS_WECHAT_OFFICIAL_TOKEN_CALLBACK_URL', ''), //回调地址
                'cache_path'     => env('TOOLS_WECHAT_OFFICIAL_CACHE_PATH', ''), //插件 缓存目录
            ],
        ],
        // 开发平台
        'open_platform'    => [
            'default' => [
                'app_id'  => env('TOOLS_WECHAT_OPEN_PLATFORM_APPID', ''),
                'secret'  => env('TOOLS_WECHAT_OPEN_PLATFORM_SECRET', ''),
                'token'   => env('TOOLS_WECHAT_OPEN_PLATFORM_TOKEN', ''),
                'aes_key' => env('TOOLS_WECHAT_OPEN_PLATFORM_AES_KEY', ''),
            ],
        ],
        // 小程序
        'mini_program'     => [
            'default' => [
                'app_id'  => env('TOOLS_WECHAT_MINI_PROGRAM_APPID', ''),
                'secret'  => env('TOOLS_WECHAT_MINI_PROGRAM_SECRET', ''),
                'token'   => env('TOOLS_WECHAT_MINI_PROGRAM_TOKEN', ''),
                'aes_key' => env('TOOLS_WECHAT_MINI_PROGRAM_AES_KEY', ''),
            ],
        ],
        // 微信支付
        'payment'          => [
            'default' => [
                'sandbox'    => env('TOOLS_WECHAT_PAYMENT_SANDBOX', false),
                'app_id'     => env('TOOLS_WECHAT_PAYMENT_APPID', ''),
                'mch_id'     => env('TOOLS_WECHAT_PAYMENT_MCH_ID', 'your-mch-id'),
                'key'        => env('TOOLS_WECHAT_PAYMENT_KEY', 'key-for-signature'),
                'cert_path'  => env('TOOLS_WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'),    // XXX: 绝对路径！！！！
                'key_path'   => env('TOOLS_WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'),      // XXX: 绝对路径！！！！
                'notify_url' => 'http://example.com/payments/wechat-notify',                           // 默认支付结果通知地址
            ],
        ],
        // 企业微信
        'work'             => [
            'default' => [
                'corp_id'  => env('TOOLS_WECHAT_WORK_CORP_ID'),
                'agent_id' => env('TOOLS_WECHAT_WORK_AGENT_ID', 100020),
                'secret'   => env('TOOLS_WECHAT_WORK_AGENT_CONTACTS_SECRET', ''),
                //...
            ],
        ],
    ],

    //  mysql
    'mysql'     => [
        'default' => [
            'host'     => env('TOOLS_MYSQL_HOST', '127.0.0.1'),
            'username' => env('TOOLS_MYSQL_HOST', 'root'),
            'password' => env('TOOLS_MYSQL_HOST', ''),
            'db'       => env('TOOLS_MYSQL_HOST', 'test'),
            'port'     => env('TOOLS_MYSQL_HOST', 3306),
            'prefix'   => env('TOOLS_MYSQL_HOST', ''),
            'charset'  => env('TOOLS_MYSQL_HOST', 'utf8'),
        ],
    ],

    // 截图程序
    'phantomjs' => [
        'default' => [
            'path' => env('TOOLS_PHANTOMJS_PATH', ''), // 'phantomjs.exe' 或者 'phantomjs' 存放的绝对路径
        ],
    ],
    'redis'     => [
        'default' => [
            'host'    => env('TOOLS_REDIS_HOST', ''),
            'port'    => env('TOOLS_REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('TOOLS_REDIS_AUTH', ''),
        ],
    ],
    // 短信
    'sms'       => [
        'aliyun'  => [
            'access_app_id' => env('TOOLS_SMS_ALI_APP_ID', ''), // accessKeyId
            'secret'        => env('TOOLS_SMS_ALI_SECRET', ''), // accessKeySecret
            'sign'          => env('TOOLS_SMS_ALI_SIGN', ''), // 签名
        ],
        'tencent' => [
            'access_app_id' => env('TOOLS_SMS_TENCENT_APP_ID', ''), // accessKeyId
            'secret'        => env('TOOLS_SMS_TENCENT_SECRET', ''), // accessKeySecret
            'sign'          => env('TOOLS_SMS_TENCENT_SIGN', ''), // 签名
        ],
        // ...
    ],
    // 邮件配置
    'mail'      => [
        'default' => [
            'host'        => env('TOOLS_MAIL_HOST', 'smtp.qq.com'), //stmp服务地址
            'username'    => env('TOOLS_MAIL_USERNAME', ''), // 登录邮箱的账号
            'password'    => env('TOOLS_MAIL_PASSWORD', ''),//客户端授权密码，注意不是登录密码
            'smtp_secure' => env('TOOLS_MAIL_SMTP_SECURE', 'ssl'),//使用ssl协议
            'smtp_auth'   => env('TOOLS_MAIL_SMTP_AUTH', true),//设置是否进行权限校验
            'port'        => env('TOOLS_MAIL_PORT', '465'),//端口设置
            'form'        => env('TOOLS_MAIL_FROM', '威四方'),//邮件来源，例如 威四方
        ],
    ],
];

