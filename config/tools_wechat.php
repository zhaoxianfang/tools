<?php
// 微信生态类型
return [
    /////////////////
    //微信 配置，根据不同的业务定义不同的键名，默认读取 default
    ////////////////
    // SDK 初始化时候会默认使用 default 键名的配置，如果需要使用其他键名的配置，需要在初始化时候指定
    'default'      => [
        // 公共配置
        "appid"            => env('OAUTH_WECHAT_APPID', ''), // 应用ID
        "notify_url"       => env('OAUTH_WECHAT_NOTIFY_URL', ''), // 默认的异步通知地址，通知URL必须为直接可访问的URL，不允许携带查询串，要求必须为https地址
        'cache_path'       => env('OAUTH_WECHAT_CACHE_PATH', ''),// 缓存目录配置（可选，需拥有读写权限）

        // 设置支付商模式 service:服务商模式 merchant:普通商户模式，不填默认为普通商户模式
        "mode"             => 'merchant',

        // 公众号配置
        'secret'           => env('OAUTH_WECHAT_SECRET', ''),
        'token'            => env('OAUTH_WECHAT_TOKEN', 'token12345'),
        'aes_key'          => env('OAUTH_WECHAT_AES_KEY', ''),

        // =========== ⇩⇩⇩支付模块配置⇩⇩⇩===========
        // 商户证书:微信V2支付和V3支付共用的证书配置
        'apiclient_key'    => env('OAUTH_WECHAT_PAY_PRIVATE_PATH', ''), // 商户私钥('.cer', '.crt', '.pem' 后缀的证书文件绝对路径)
        'apiclient_cert'   => env('OAUTH_WECHAT_PAY_PUBLIC_PATH', ''), // 商户公钥('.cer', '.crt', '.pem' 后缀的证书文件绝对路径)

        // 普通商户
        "mchid"            => env('OAUTH_WECHAT_PAY_MCH_ID', ''), // 商户号ID

        // 服务商
        "sp_appid"         => env('OAUTH_WECHAT_PAY_SP_APP_ID', ''), // 服务商应用ID
        "sp_mchid"         => env('OAUTH_WECHAT_PAY_SP_MCH_ID', ''), // 服务商户号
        "sub_mchid"        => env('OAUTH_WECHAT_PAY_SUB_MCH_ID', ''), // 子商户号(可选，可在请求参数中传入)

        // 微信V3配置
        "v3_secret_key"    => env('OAUTH_WECHAT_PAY_V3_SECRET_KEY', ''), // 在商户平台上手动输入设置的APIv3密钥:账户中心->API安全中心->APIv3密钥->设置密钥
        "wechatpay_serial" => env('OAUTH_WECHAT_PAY_V3_SERIAL_PATH', ''), // 可以调用 WechatPayFactory::JsApi('default')->getCert('你的证书保存文件夹路径') 获取
    ],
    // 小程序,自定义键名
    'mini_program' => [
        // ...
    ],
    'work'         => [
        'corp_id' =>  env('OAUTH_WECHAT_WORK_CORP_ID', ''), // 企业ID
        'secret'  =>  env('OAUTH_WECHAT_WORK_SECRET', ''),  // 企业secret
        'token'   =>  env('OAUTH_WECHAT_WORK_TOKEN', ''),
    ],
];