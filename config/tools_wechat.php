<?php
// 微信生态类型
return [
    /////////////////
    //微信 配置，根据不同的业务定义不同的键名
    ////////////////

    'default'      => [
        'app_id'  => env('TOOLS_WECHAT_OPEN_PLATFORM_APPID', ''),
        'secret'  => env('TOOLS_WECHAT_OPEN_PLATFORM_SECRET', ''),
        'token'   => env('TOOLS_WECHAT_OPEN_PLATFORM_TOKEN', ''),
        'aes_key' => env('TOOLS_WECHAT_OPEN_PLATFORM_AES_KEY', ''),
    ],
    // 小程序,自定义键名
    'mini_program' => [
        // ...
    ],
];