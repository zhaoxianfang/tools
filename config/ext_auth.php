<?php
// 授权、登录相关的配置
return [
    //微博Web
    'sina' => [
        'default' => [
            'wb_akey'         => env('EXT_SINA_WEB_AKEY', ''),
            'wb_skey'         => env('EXT_SINA_WEB_SKEY', ''),
            'wb_callback_url' => env('EXT_SINA_WEB_CALLBACK_URL', ''), //回调
        ],
    ],
    //QQ
    'qq'   => [
        'default' => [
            'client_id'     => env('EXT_QQ_WEB_APP_ID', ''),
            'client_secret' => env('EXT_QQ_WEB_APP_KEY', ''),
            'redirect_uri'  => env('EXT_QQ_WEB_CALLBACK_URL', ''),
        ],
        'mobile'  => [
            'client_id'     => env('EXT_QQ_MOBILE_APP_ID', ''),
            'client_secret' => env('EXT_QQ_MOBILE_APP_KEY', ''),
        ],
    ],
];