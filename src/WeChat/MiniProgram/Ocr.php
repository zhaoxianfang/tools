<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序ORC服务
 */
class Ocr extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 本接口提供基于小程序的银行卡 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/bankCardOCR.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function bankcard(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/bankcard', $data);
    }

    /**
     * 本接口提供基于小程序的营业执照 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/businessLicenseOCR.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function businessLicense(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/bizlicense', $data);
    }

    /**
     * 本接口提供基于小程序的驾驶证 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/driverLicenseOCR.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function driverLicense(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/drivinglicense', $data);
    }

    /**
     * 本接口提供基于小程序的身份证 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/idCardOCR.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function idcard(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/idcard', $data);
    }

    /**
     * 本接口提供基于小程序的通用印刷体 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/printedTextOCR.html
     *
     * @param  string  $img_url  要检测的图片 url，传这个则不用传 img 参数。
     * @param  string|null  $img  form-data 中媒体文件标识，有filename、filelength、content-type等信息，传这个则不用穿 img_url
     * @return array
     *
     * @throws Exception
     */
    public function printedText(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/comm', $data);
    }

    /**
     * 本接口提供基于小程序的行驶证 OCR 识别
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/img-ocr/ocr/vehicleLicenseOCR.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function vehicleLicense(string $img_url, ?string $img)
    {
        $data = ['img_url' => $img_url, 'img' => $img];

        return $this->post('cv/ocr/driving', $data);
    }
}
