<?php

// +---------------------------------------------------------------------
// | 随机数
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

namespace zxf\Tools;

/**
 * 随机生成类
 */
class Random
{
    /**
     * 生成数字和字母
     *
     * @param  int  $len  长度
     * @return string
     */
    public static function alnum(int $len = 6)
    {
        return self::build('alnum', $len);
    }

    /**
     * 仅生成字符
     *
     * @param  int  $len  长度
     * @return string
     */
    public static function alpha(int $len = 6)
    {
        return self::build('alpha', $len);
    }

    /**
     * 生成指定长度的随机数字
     *
     * @param  int  $len  长度
     * @return string
     */
    public static function numeric(int $len = 4)
    {
        return self::build('numeric', $len);
    }

    /**
     * 数字和字母组合的随机字符串
     *
     * @param  int  $len  长度
     * @return string
     */
    public static function nozero(int $len = 4)
    {
        return self::build('nozero', $len);
    }

    /**
     * 能用的随机数生成
     *
     * @param  string  $type  类型 alpha/alnum/numeric/nozero/unique/md5/encrypt/sha1
     * @param  int  $len  长度
     * @return string
     */
    public static function build(string $type = 'alnum', int $len = 8)
    {
        switch ($type) {
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
            default:
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
    }

    /**
     * 获取全球唯一标识
     *
     * @return string
     */
    public static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0x0FFF) | 0x4000, mt_rand(0, 0x3FFF) | 0x8000, mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );
    }
}
