<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序ORC服务
 * Class Ocr
 *
 * @package WeMini
 */
class Ocr extends WeChatBase
{
    /**
     * 本接口提供基于小程序的银行卡 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function bankcard($data)
    {
        return $this->post("cv/ocr/bankcard", $data);
    }

    /**
     * 本接口提供基于小程序的营业执照 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function businessLicense($data)
    {
        return $this->post("cv/ocr/bizlicense", $data);
    }

    /**
     * 本接口提供基于小程序的驾驶证 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function driverLicense($data)
    {
        return $this->post("cv/ocr/drivinglicense", $data);
    }

    /**
     * 本接口提供基于小程序的身份证 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function idcard($data)
    {
        return $this->post("cv/ocr/idcard", $data);
    }

    /**
     * 本接口提供基于小程序的通用印刷体 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function printedText($data)
    {
        return $this->post("cv/ocr/comm", $data);
    }

    /**
     * 本接口提供基于小程序的行驶证 OCR 识别
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function vehicleLicense($data)
    {
        return $this->post("cv/ocr/driving", $data);
    }
}