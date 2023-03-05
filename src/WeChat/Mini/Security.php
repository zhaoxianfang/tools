<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序内容安全
 * Class Security
 *
 * @package WeMini
 */
class Security extends WeChatBase
{

    /**
     * 校验一张图片是否含有违法违规内容
     *
     * @param string $media 要检测的图片文件，格式支持PNG、JPEG、JPG、GIF，图片尺寸不超过 750px x 1334px
     *
     * @return array
     * @throws Exception
     */
    public function imgSecCheck($media)
    {
        return $this->post("wxa/img_sec_check", ["media" => $media]);
    }

    /**
     * 异步校验图片/音频是否含有违法违规内容
     *
     * @param string $media_url
     * @param string $media_type
     *
     * @return array
     * @throws Exception
     */
    public function mediaCheckAsync($media_url, $media_type)
    {
        return $this->post("wxa/media_check_async", ["media_url" => $media_url, "media_type" => $media_type]);
    }

    /**
     * 检查一段文本是否含有违法违规内容
     *
     * @param string $content
     *
     * @return array
     * @throws Exception
     */
    public function msgSecCheck($content)
    {
        return $this->post("wxa/msg_sec_check", ["content" => $content]);
    }
}