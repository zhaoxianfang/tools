<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序ORC服务
 * Class Ocr
 * @package WeMini
 */
class Ocr extends WeChatBase
{
    /**
     * 本接口提供基于小程序的银行卡 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function bankcard($data)
    {
        $url = 'cv/ocr/bankcard';
        return $this->post($url, $data);
    }

    /**
     * 本接口提供基于小程序的营业执照 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function businessLicense($data)
    {
        $url = 'cv/ocr/bizlicense';
        return $this->post($url, $data);
    }

    /**
     * 本接口提供基于小程序的驾驶证 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function driverLicense($data)
    {
        $url = 'cv/ocr/drivinglicense';
        return $this->post($url, $data);
    }

    /**
     * 本接口提供基于小程序的身份证 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function idcard($data)
    {
        $url = 'cv/ocr/idcard';
        return $this->post($url, $data);
    }

    /**
     * 本接口提供基于小程序的通用印刷体 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function printedText($data)
    {
        $url = 'cv/ocr/comm';
        return $this->post($url, $data);
    }

    /**
     * 本接口提供基于小程序的行驶证 OCR 识别
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function vehicleLicense($data)
    {
        $url = 'cv/ocr/driving';
        return $this->post($url, $data);
    }
}