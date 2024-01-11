<?php
/**
 * 发送邮件通知配置
 */

return [

    /**
     * 默认使用的邮件服务
     * 值为 mailers 中的 key 或者 'fail_over' (故障转移列表)
     */
    'default'   => 'smtp',

    /**
     * 可用的邮件服务列表
     * 有n个邮件服务时，就配置n个项
     */
    'mailers'   => [
        // 邮件服务的配置,键名自定义
        'smtp' => [
            'mailer'      => env('MAIL_MAILER', 'smtp'), //邮件服务器，支持 stmp、mail、sendmail、qmail 4种
            'host'        => env('MAIL_HOST', 'smtp.qq.com'), // 服务地址
            'username'    => env('MAIL_USERNAME', ''), // 登录邮箱的账号
            'password'    => env('MAIL_PASSWORD', ''),//客户端授权密码，注意不是登录密码
            'smtp_secure' => env('MAIL_ENCRYPTION', 'ssl'),//可配置ssl或tls协议
            'smtp_auth'   => env('MAIL_SMTP_AUTH', true),//设置是否进行权限校验
            'port'        => env('MAIL_PORT', '465'),//端口设置
        ],
        // 实际应用中，参照上面的smtp配置把 ... 替换成具体的配置
        '...'  => [
            // ...
        ],
    ],

    /**
     * 邮件发送者
     */
    'from'      => [
        'name'    => env('MAIL_FROM_NAME', 'Example'), // 发送者名称(一般配置系统名称)
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),// 发送者邮箱号
    ],

    /**
     * 故障转移
     * 按照顺序依次发送，直到成功或全部都试一遍
     * 数组内的值为 mailers 中的 key
     */
    'fail_over' => [
        'smtp',
    ],
];
