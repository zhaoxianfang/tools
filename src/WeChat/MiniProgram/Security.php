<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 小程序内容安全
 */
class Security extends WeChatBase
{
    public $useToken = true;

    /**
     * 校验一张图片是否含有违法违规内容
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/framework/security.imgSecCheck.html#HTTPS%20%E8%B0%83%E7%94%A8
     *
     * @param string $filePath 要检测的图片文件，格式支持PNG、JPEG、JPG、GIF，图片尺寸不超过 750px x 1334px
     *
     * @return array
     * @throws Exception
     */
    public function imgSecCheck(string $filePath)
    {
        return $this->httpUpload('wxa/img_sec_check', $filePath);
    }

    /**
     * 异步校验图片/音频是否含有违法违规内容
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/sec-check/mediaCheckAsync.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function mediaCheckAsync(array $data)
    {
        return $this->post('wxa/media_check_async', $data);
    }

    /**
     * 检查一段文本是否含有违法违规内容
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/sec-check/msgSecCheck.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function msgSecCheck(array $data)
    {
        return $this->post('wxa/msg_sec_check', $data);
    }

    /**
     * 获取用户安全等级
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/safety-control-capability/getUserRiskRank.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getUserRiskRank(array $data)
    {
        return $this->post('wxa/getuserriskrank', $data);
    }
}