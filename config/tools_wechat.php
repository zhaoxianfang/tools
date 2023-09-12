<?php
// 微信生态类型
return [
    /////////////////
    //微信 配置，根据不同的业务定义不同的键名，默认读取 default
    ////////////////
    // SDK 初始化时候会默认使用 default 键名的配置，如果需要使用其他键名的配置，需要在初始化时候指定
    'default'      => [
        // 公共配置
        "appid"            => env('TOOLS_WECHAT_OPEN_PLATFORM_APPID', ''), // 应用ID
        "notify_url"       => "", // 默认的异步通知地址，通知URL必须为直接可访问的URL，不允许携带查询串，要求必须为https地址
        'cache_path'       => '',// 缓存目录配置（可选，需拥有读写权限）

        // 公众号配置
        'secret'           => env('TOOLS_WECHAT_OPEN_PLATFORM_SECRET', ''),
        'token'            => env('TOOLS_WECHAT_OPEN_PLATFORM_TOKEN', ''),
        'aes_key'          => env('TOOLS_WECHAT_OPEN_PLATFORM_AES_KEY', ''),

        // =========== ⇩⇩⇩支付模块配置⇩⇩⇩===========
        // 商户证书:微信V2支付和V3支付共用的证书配置
        'apiclient_key'    => '', // 商户私钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)
        'apiclient_cert'   => '', // 商户公钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)

        // 普通商户
        "mchid"            => "", // 商户号ID

        // 服务商
        "sp_appid"         => "", // 服务商应用ID
        "sp_mchid"         => "", // 服务商户号
        "sub_mchid"        => "", // 子商户号(可选，可在请求参数中传入)

        // 微信V3配置
        "v3_secret_key"    => "", // 在商户平台上手动输入设置的APIv3密钥
        "wechatpay_serial" => "", // 可以调用 WechatPayFactory::JsApi('default')->getCert('你的证书保存文件夹路径') 获取
    ],
    // 小程序,自定义键名
    'mini_program' => [
        // ...
    ],
];