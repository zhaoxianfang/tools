<?php

// 未归类的配置项

return [
    // 截图程序
    'phantomjs' => env('OAUTH_PHANTOMJS_PATH', ''), // 'phantomjs.exe' 或者 'phantomjs' 存放的绝对路径 例如 /www/soft

    // 默认缓存路径
    'cache_path' => is_laravel() ? config('cache.stores.file.path', storage_path('framework/cache/data')) : __DIR__.'/cache',

    // 默认字体文件存放路径
    'font_dir' => __DIR__.'/font',

    // ====================================================
    // 通知类型的配置 短信、邮件等通知
    // ====================================================
    // 短信通知
    'sms' => [
        'aliyun' => [
            'app_id' => env('OAUTH_SMS_ALI_APP_ID', ''), // accessKeyId
            'secret' => env('OAUTH_SMS_ALI_SECRET', ''), // accessKeySecret
            'sign' => env('OAUTH_SMS_ALI_SIGN', ''), // 签名
        ],
        'tencent' => [
            'app_id' => env('OAUTH_SMS_TENCENT_APP_ID', ''), // accessKeyId
            'secret' => env('OAUTH_SMS_TENCENT_SECRET', ''), // accessKeySecret
            'sign' => env('OAUTH_SMS_TENCENT_SIGN', ''), // 签名
        ],
        // ...
    ],
];
