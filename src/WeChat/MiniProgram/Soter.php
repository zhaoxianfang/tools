<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序生物认证
 */
class Soter extends WeChatBase
{
    public bool $useToken = false;

    /**
     * SOTER 生物认证秘钥签名验证
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/soter/verifySignature.html
     *
     * @param  string  $openid  用户 openid
     * @param  string  $json_string  通过 wx.startSoterAuthentication 成功回调获得的 resultJSON 字段
     * @param  string  $json_signature  通过 wx.startSoterAuthentication 成功回调获得的 resultJSONSignature 字段
     * @return array
     *
     * @throws Exception
     */
    public function verifySignature(string $openid, string $json_string, string $json_signature)
    {
        $data = [
            'openid' => $openid,
            'json_string' => $json_string,
            'json_signature' => $json_signature,
        ];

        return $this->post('cgi-bin/soter/verify_signature', $data);
    }
}
