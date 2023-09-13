<?php

namespace zxf\Pay\Traits;

/**
 * 服务商/合作伙伴特有的请求头信息 Wechatpay-Serial
 */
trait withHeaderSerialTrait
{

    protected bool $useSerialHeader = false;// 是否使用 Wechatpay-Serial 请求头

    /**
     * 是否使用 Wechatpay-Serial 请求头
     */
    public function withHeaderSerial($useSerialHeader = true)
    {
        $this->useSerialHeader = (bool)$useSerialHeader;
        return $this;
    }

    /**
     * 获取 Wechatpay-Serial 请求头
     */
    public function getSerialHeader(): array
    {
        $mchPublicCertPath = $this->config['apiclient_cert'];// 公钥商户序列号
        return [
            'Wechatpay-Serial' => $this->getSerialNo($mchPublicCertPath),
        ];
    }
}