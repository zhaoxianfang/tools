<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序图像处理
 */
class Image extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 本接口提供基于小程序的图片智能裁剪能力
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/img/aiCrop.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param  string  $img_url  要检测的图片 url，传这个则不用传 img 参数。
     * @param  string|null  $img  form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     *
     * @throws Exception
     */
    public function aiCrop(string $img_url, ?string $img)
    {
        return $this->post('cv/img/aicrop', ['img_url' => $img_url, 'img' => $img]);
    }

    /**
     * 本接口提供基于小程序的条码/二维码识别的API
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/img/scanQRCode.html
     *
     * @param  string  $img_url  要检测的图片 url，传这个则不用传 img 参数。
     * @param  string|null  $img  form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     *
     * @throws Exception
     */
    public function scanQRCode(string $img_url, ?string $img)
    {
        return $this->post('cv/img/qrcode', ['img_url' => $img_url, 'img' => $img]);
    }

    /**
     * 高清化图片
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/img/superResolution.html
     *
     * @param  string  $img_url  要检测的图片 url，传这个则不用传 img 参数。
     * @param  string|null  $img  form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     *
     * @throws Exception
     */
    public function superResolution(string $img_url, ?string $img)
    {
        return $this->post('cv/img/superresolution', ['img_url' => $img_url, 'img' => $img]);
    }

    /**
     * 上传临时图片
     *
     * @param  string  $filePath  文件路径
     * @return array|bool|mixed|string
     *
     * @throws Exception
     */
    public function uploadImage(string $filePath)
    {
        $res = $this->upload(10, $filePath, 'image');
        if (! empty($res['errcode'])) {
            throw new Exception($this->getMessage($res['errcode']), $res['errcode']);
        }

        return $res; // 返回 type、media_id、created_at
    }
}
