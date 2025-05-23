<?php

namespace zxf\Login\Constants;

class ConstCode
{
    /** 公共状态 */
    const STATUS_DELETE = 0; // 删除

    const STATUS_NORMAL = 1; // 正常

    /** 性别 */
    const GENDER = 0; // 未知

    const GENDER_MAN = 1; // 男

    const GENDER_WOMEN = 2; // 女

    /** 登录类型 */
    const TYPE_QQ = 1; // QQ

    const TYPE_WECHAT = 2; // 微信

    const TYPE_WECHAT_MOBILE = 3; // 微信mobile

    const TYPE_SINA = 4; // sina新浪微博

    const TYPE_GITHUB = 5; // GitHub

    const TYPE_ALIPAY = 6; // AliPay

    const TYPE_FACEBOOK = 7; // faceBook

    const TYPE_GOOGLE = 8; // google

    const TYPE_TWITTER = 9; // 飞鸽

    const TYPE_LINE = 10; // line

    const TYPE_NAVER = 11; // naver

    const TYPE_ALIYUN = 12; // 阿里云

    const TYPE_BAIDU = 13; // 百度

    const TYPE_CODING = 14; // Coding

    const TYPE_CSDN = 15; // CSDN

    const TYPE_GITEE = 16; // Gitee

    const TYPE_GITLAB = 17; // GitLab

    const TYPE_OSCHINA = 18; // OSChina

    const TYPE_QQ_APP = 21; // qqAPP

    const TYPE_WECHAT_APP = 22; // 微信APP

    const TYPE_WECHAT_APPLETS = 23; // 微信小程序

    const TYPE_DOUYIN = 31; // 抖音

    const TYPE_TOUTIAO = 32; // 头条

    const TYPE_XIGUA = 33; // 西瓜

    /**
     * Description:  getTypeConst
     *
     * @param  int  $channel  渠道：登录方式
     * @param  bool|string  $type  类型：app applets
     * @return int
     */
    public static function getTypeConst(int $channel, bool|string $type = '')
    {
        switch ($channel) {
            case self::TYPE_QQ:
                if ($type == 'app') {
                    $typeConst = self::TYPE_QQ_APP; // qqApp
                } else {
                    $typeConst = $channel;
                }
                break;
            case self::TYPE_WECHAT:
                if ($type == 'app') {
                    $typeConst = self::TYPE_WECHAT_APP; // 微信App
                } elseif ($type == 'applets') {
                    $typeConst = self::TYPE_WECHAT_APPLETS; // 微信小程序
                } elseif (is_wechat_browser()) {
                    $typeConst = self::TYPE_WECHAT_MOBILE; // 微信mobile
                } else {
                    $typeConst = $channel;
                }
                break;
            default:
                $typeConst = $channel;
                break;
        }

        return $typeConst;
    }
}
