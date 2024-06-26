<?php
// 授权、登录相关的配置
return [
    //微博Web
    'sina' => [
        'default' => [
            'wb_akey'         => env('TOOLS_SINA_WEB_AKEY', ''),
            'wb_skey'         => env('TOOLS_SINA_WEB_SKEY', ''),
            'wb_callback_url' => env('TOOLS_SINA_WEB_CALLBACK_URL', ''), //回调
        ],
    ],
    //QQ
    'qq'   => [
        'default' => [
            'client_id'     => env('TOOLS_QQ_WEB_APP_ID', ''),
            'client_secret' => env('TOOLS_QQ_WEB_APP_KEY', ''),
            'redirect_uri'  => env('TOOLS_QQ_WEB_CALLBACK_URL', ''),
        ],
        'mobile'  => [
            'client_id'     => env('TOOLS_QQ_MOBILE_APP_ID', ''),
            'client_secret' => env('TOOLS_QQ_MOBILE_APP_KEY', ''),
        ],
    ],
    // 微信Wechat 使用 tools_wechat配置文件中的配置

];