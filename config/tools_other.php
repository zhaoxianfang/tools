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
            'host'        => env('TOOLS_MAIL_HOST', 'smtp.qq.com'), //stmp服务地址
            'username'    => env('TOOLS_MAIL_USERNAME', ''), // 登录邮箱的账号
            'password'    => env('TOOLS_MAIL_PASSWORD', ''),//客户端授权密码，注意不是登录密码
            'smtp_secure' => env('TOOLS_MAIL_SMTP_SECURE', 'ssl'),//使用ssl协议
            'smtp_auth'   => env('TOOLS_MAIL_SMTP_AUTH', true),//设置是否进行权限校验
            'port'        => env('TOOLS_MAIL_PORT', '465'),//端口设置
            'form'        => env('TOOLS_MAIL_FROM', '威四方'),//邮件来源，例如 威四方
        ],
    ],

    // ====================================================
    // 数据库相关的配置，mysql、redis、elastic 等
    // ====================================================
    'redis'      => [
        'default' => [
            'host'    => env('TOOLS_REDIS_HOST', ''),
            'port'    => env('TOOLS_REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('TOOLS_REDIS_AUTH', ''),
        ],
    ],
    //  mysql
    'mysql'      => [
        'default' => [
            'host'     => env('TOOLS_MYSQL_HOST', '127.0.0.1'),
            'username' => env('TOOLS_MYSQL_HOST', 'root'),
            'password' => env('TOOLS_MYSQL_HOST', ''),
            'db'       => env('TOOLS_MYSQL_HOST', 'test'),
            'port'     => env('TOOLS_MYSQL_HOST', 3306),
            // 'prefix'   => env('TOOLS_MYSQL_HOST', ''),
            'charset'  => env('TOOLS_MYSQL_HOST', 'utf8mb4'),
            'socket'   => env('TOOLS_MYSQL_SOCKET', null),
        ],
    ],
];