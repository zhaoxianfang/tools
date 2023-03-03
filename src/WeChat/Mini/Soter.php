<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序生物认证
 * Class Soter
 *
 * @package WeMini
 */
class Soter extends WeChatBase
{
    /**
     * SOTER 生物认证秘钥签名验证
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function verifySignature($data)
    {
        return $this->post('cgi-bin/soter/verify_signature', $data);
    }
}