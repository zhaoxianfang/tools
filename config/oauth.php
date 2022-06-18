<?php
/**
 * 第三方登录回调配置参数
 */
return [
    //微博
    'sina'   => [
        'wb_akey'         => '',
        'wb_skey'         => '',
        'wb_callback_url' => '', //回调
    ],
    //QQ
    'qq'     => [
        'appid'       => '',
        'appkey'      => '',
        'callbackUrl' => '',
    ],
    //微信
    'wechat' => [
        'token'                  => '', //填写你设定的key
        'encodingaeskey'         => '', //填写加密用的EncodingAESKey
        'appid'                  => '', //填写高级调用功能的app id
        'appsecret'              => '', //填写高级调用功能的密钥
        'GetAccessTokenCallback' => '', //回调地址
        'cache_path'             => '', //插件 缓存目录
    ],

];
