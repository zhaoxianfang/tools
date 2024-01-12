<?php

namespace zxf\Sms;

// use zxf\Sms\AliYunSms;
// use zxf\Sms\TencentSms;

class Sms
{

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 初始化
     * @param string $key accessKeyId
     * @param string $secret accessSecret
     * @param string $driveType 驱动类型 ali(阿里云)[默认] 或者 tencent（腾讯云）
     * @return Sms
     */
    public static function instance($key, $secret, $driveType = 'ali')
    {
        // 驱动类型 ali or tencent
        if ($driveType == 'ali') {
            $driveClass = AliYunSms::class;
        } else {
            $driveClass = TencentSms::class;
        }

        self::$instance = $driveClass::instance($key, $secret);

        return self::$instance;
    }
}
