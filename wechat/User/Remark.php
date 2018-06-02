<?php

namespace zxf\tool\Wechat\User;

use zxf\tool\Wechat\Bridge\Http;
use zxf\tool\Wechat\Wechat\AccessToken;

class Remark
{
    /**
     * 设置用户备注名.
     */
    const REMARK = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark';

    /**
     * zxf\tool\Wechat\Wechat\AccessToken.
     */
    protected $accessToken;

    /**
     * 构造方法.
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 设置/更新用户备注.
     */
    public function update($openid, $remark)
    {
        $body = [
            'openid' => $openid,
            'remark' => $remark,
        ];

        $response = Http::request('POST', static::REMARK)
            ->withAccessToken($this->accessToken)
            ->withBody($body)
            ->send();

        if (0 != $response['errcode']) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}
