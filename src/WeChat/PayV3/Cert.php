<?php


namespace zxf\WeChat\PayV3;

use Exception;
use zxf\WeChat\PayV3\Contracts\BasicWePay;
use zxf\WeChat\PayV3\Contracts\DecryptAes;

/**
 * 平台证书管理
 */
class Cert extends BasicWePay
{
    /**
     * 商户平台下载证书
     *
     * @return void
     * @throws Exception
     */
    public function download()
    {
        try {
            $aes    = new DecryptAes($this->config["mch_v3_key"]);
            $result = $this->doRequest("GET", "/v3/certificates");
            foreach ($result["data"] as $vo) {
                $this->tmpFile($vo["serial_no"], $aes->decryptToString(
                    $vo["encrypt_certificate"]["associated_data"],
                    $vo["encrypt_certificate"]["nonce"],
                    $vo["encrypt_certificate"]["ciphertext"]
                ));
            }
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}