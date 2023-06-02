<?php
// 微信生态类型
return [
    //微信
    'wechat' => [
        // 公众号
        'official_account' => [
            'default' => [
                'token'          => env('EXT_WECHAT_OFFICIAL_TOKEN', ''), //填写你设定的key
                'appid'          => env('EXT_WECHAT_OFFICIAL_APP_ID', ''), //填写高级调用功能的app id
                'appsecret'      => env('EXT_WECHAT_OFFICIAL_APP_SECRET', ''), //填写高级调用功能的密钥
                'encodingaeskey' => env('EXT_WECHAT_OFFICIAL_AES_KEY', ''), //填写加密用的EncodingAESKey
                // 配置商户支付参数（可选，在使用支付功能时需要）
                'mch_id'         => "1235704602",
                'mch_key'        => 'IKI4kpHjU94ji3oqre5zYaQMwLHuZPmj',
                // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
                'ssl_key'        => '',
                'ssl_cer'        => '',
                // 缓存目录配置（可选，需拥有读写权限）
                'cache_path'     => env('EXT_WECHAT_OFFICIAL_CACHE_PATH', ''), //插件 缓存目录
                'token_callback' => env('EXT_WECHAT_OFFICIAL_TOKEN_CALLBACK_URL', ''), //回调地址
            ],
        ],
        // 开发平台
        'open_platform'    => [
            'default' => [
                'app_id'  => env('EXT_WECHAT_OPEN_PLATFORM_APPID', ''),
                'secret'  => env('EXT_WECHAT_OPEN_PLATFORM_SECRET', ''),
                'token'   => env('EXT_WECHAT_OPEN_PLATFORM_TOKEN', ''),
                'aes_key' => env('EXT_WECHAT_OPEN_PLATFORM_AES_KEY', ''),
            ],
        ],
        // 小程序
        'mini_program'     => [
            'default' => [
                'app_id'  => env('EXT_WECHAT_MINI_PROGRAM_APPID', ''),
                'secret'  => env('EXT_WECHAT_MINI_PROGRAM_SECRET', ''),
                'token'   => env('EXT_WECHAT_MINI_PROGRAM_TOKEN', ''),
                'aes_key' => env('EXT_WECHAT_MINI_PROGRAM_AES_KEY', ''),
            ],
        ],
        // 微信支付
        'payment'          => [
            'default' => [
                'sandbox'    => env('EXT_WECHAT_PAYMENT_SANDBOX', false),
                'app_id'     => env('EXT_WECHAT_PAYMENT_APPID', ''),
                'mch_id'     => env('EXT_WECHAT_PAYMENT_MCH_ID', 'your-mch-id'),
                'key'        => env('EXT_WECHAT_PAYMENT_KEY', 'key-for-signature'),
                'cert_path'  => env('EXT_WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'),    // XXX: 绝对路径！！！！
                'key_path'   => env('EXT_WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'),      // XXX: 绝对路径！！！！
                'notify_url' => 'http://example.com/payments/wechat-notify',                           // 默认支付结果通知地址
            ],
        ],
        // 企业微信
        'work'             => [
            'default' => [
                'corp_id'  => env('EXT_WECHAT_WORK_CORP_ID'),
                'agent_id' => env('EXT_WECHAT_WORK_AGENT_ID', 100020),
                'secret'   => env('EXT_WECHAT_WORK_AGENT_CONTACTS_SECRET', ''),
                //...
            ],
        ],
    ],
];