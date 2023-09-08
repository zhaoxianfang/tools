<?php

// 支付相关的配置
return [
    // 微信支付
    'wechat' => [
        'default' => [
            // 普通商户
            "appid"     => "", // 应用ID
            "mchid"     => "", // 直连商户号

            // 服务商
            "sp_appid"  => "", // 服务商应用ID
            "sp_mchid"  => "", // 服务商户号
            "sub_mchid" => "", // 子商户号(可选，可在请求参数中传入)

            "v3_secret_key"    => "", // 在商户平台上设置的APIv3密钥
            'mch_private_cert' => '', // 商户私钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)
            'mch_public_cert'  => '', // 商户公钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)
            "notify_url"       => "", // 默认的异步通知地址，通知URL必须为直接可访问的URL，不允许携带查询串，要求必须为https地址

            'cache_path' => '',// 缓存目录配置（可选，需拥有读写权限）
        ],
    ],
    //  支付宝支付
    'alipay' => [
        'default' => [
            'appid' => '',
        ],
    ],
];