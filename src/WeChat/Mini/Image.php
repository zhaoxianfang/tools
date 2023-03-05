<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序图像处理
 * Class Image
 *
 * @package WeMini
 */
class Image extends WeChatBase
{

    /**
     * 本接口提供基于小程序的图片智能裁剪能力
     *
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数。
     * @param string $img     form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     *
     * @return array
     * @throws Exception
     */
    public function aiCrop($img_url, $img)
    {
        return $this->post("cv/img/aicrop", ["img_url" => $img_url, "img" => $img]);
    }

    /**
     * 本接口提供基于小程序的条码/二维码识别的API
     *
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数。
     * @param string $img     form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     *
     * @return array
     * @throws Exception
     */
    public function scanQRCode($img_url, $img)
    {
        return $this->post("cv/img/qrcode", ["img_url" => $img_url, "img" => $img]);
    }

    /**
     * 本接口提供基于小程序的图片高清化能力
     *
     * @param string $img_url 要检测的图片 url，传这个则不用传 img 参数
     * @param string $img     form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     *
     * @return array
     * @throws Exception
     */
    public function superresolution($img_url, $img)
    {
        return $this->post("cv/img/qrcode", ["img_url" => $img_url, "img" => $img]);
    }
}