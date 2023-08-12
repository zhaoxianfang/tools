<?php
// 通知类型的配置 短信、邮件等通知
return [
    // 短信
    'sms'  => [
        'aliyun'  => [
            'app_id' => env('EXT_SMS_ALI_APP_ID', ''), // accessKeyId
            'secret' => env('EXT_SMS_ALI_SECRET', ''), // accessKeySecret
            'sign'   => env('EXT_SMS_ALI_SIGN', ''), // 签名
        ],
        'tencent' => [
            'app_id' => env('EXT_SMS_TENCENT_APP_ID', ''), // accessKeyId
            'secret' => env('EXT_SMS_TENCENT_SECRET', ''), // accessKeySecret
            'sign'   => env('EXT_SMS_TENCENT_SIGN', ''), // 签名
        ],
        // ...
    ],
    // 邮件配置
    'mail' => [
        'default' => [
            'host'        => env('EXT_MAIL_HOST', 'smtp.qq.com'), //stmp服务地址
            'username'    => env('EXT_MAIL_USERNAME', ''), // 登录邮箱的账号
            'password'    => env('EXT_MAIL_PASSWORD', ''),//客户端授权密码，注意不是登录密码
            'smtp_secure' => env('EXT_MAIL_SMTP_SECURE', 'ssl'),//使用ssl协议
            'smtp_auth'   => env('EXT_MAIL_SMTP_AUTH', true),//设置是否进行权限校验
            'port'        => env('EXT_MAIL_PORT', '465'),//端口设置
            'form'        => env('EXT_MAIL_FROM', '威四方'),//邮件来源，例如 威四方
        ],
    ],
];