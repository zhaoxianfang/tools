<?php
// 未归类的配置项

return [
    // 截图程序
    'phantomjs'  => env('TOOLS_PHANTOMJS_PATH', ''), // 'phantomjs.exe' 或者 'phantomjs' 存放的绝对路径 例如 /www/soft

    // 默认缓存路径
    'cache_path' => __DIR__ . '/cache',

    // 默认字体文件存放路径
    'font_dir'   => __DIR__ . '/font',

    // ====================================================
    // 通知类型的配置 短信、邮件等通知
    // ====================================================
    // 短信通知
    'sms'        => [
        'aliyun'  => [
            'app_id' => env('TOOLS_SMS_ALI_APP_ID', ''), // accessKeyId
            'secret' => env('TOOLS_SMS_ALI_SECRET', ''), // accessKeySecret
            'sign'   => env('TOOLS_SMS_ALI_SIGN', ''), // 签名
        ],
        'tencent' => [
            'app_id' => env('TOOLS_SMS_TENCENT_APP_ID', ''), // accessKeyId
            'secret' => env('TOOLS_SMS_TENCENT_SECRET', ''), // accessKeySecret
            'sign'   => env('TOOLS_SMS_TENCENT_SIGN', ''), // 签名
        ],
        // ...
    ],
    // 邮件通知
    'mail'       => [
        'default' => [
            'mailer'      => env('MAIL_MAILER', 'smtp'), //stmp服务地址
            'host'        => env('MAIL_HOST', 'smtp.qq.com'), //stmp服务地址
            'username'    => env('MAIL_USERNAME', ''), // 登录邮箱的账号
            'password'    => env('MAIL_PASSWORD', ''),//客户端授权密码，注意不是登录密码
            'smtp_secure' => env('MAIL_ENCRYPTION', 'ssl'),//使用ssl协议
            'smtp_auth'   => env('MAIL_SMTP_AUTH', true),//设置是否进行权限校验
            'port'        => env('MAIL_PORT', '465'),//端口设置
            'form'        => env('MAIL_FROM_NAME', '威四方'),//邮件来源，例如 威四方
        ],
    ],

    // ====================================================
    // 数据库相关的配置，mysql、redis、elastic 等
    // ====================================================
    'redis'      => [
        'default' => [
            'host'    => env('REDIS_HOST', ''),
            'port'    => env('REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('REDIS_PASSWORD', ''),
        ],
    ],
    //  mysql
    'mysql'      => [
        'default' => [
            'host'     => env('DB_HOST', '127.0.0.1'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'db'       => env('DB_DATABASE', 'test'),
            'port'     => env('DB_PORT', 3306),
            // 'prefix'   => env('DB_PREFIX', ''),
            'charset'  => env('DB_CHARSET', 'utf8mb4'),
            'socket'   => env('DB_SOCKET', null),
        ],
    ],
];