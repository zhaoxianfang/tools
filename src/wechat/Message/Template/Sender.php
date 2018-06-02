<?php

namespace zxf\Wechat\Message\Template;

use zxf\Wechat\Bridge\Http;
use zxf\Wechat\Wechat\AccessToken;

class Sender
{
    /**
     * 发送接口 URL.
     */
    const SENDER = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

    /**
     * zxf\Wechat\Wechat\AccessToken.
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
     * 发送模板消息.
     */
    public function send(TemplateInterface $template)
    {
        $response = Http::request('POST', static::SENDER)
            ->withAccessToken($this->accessToken)
            ->withBody($template->getRequestBody())
            ->send();

        if (0 != $response['errcode']) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        return $response['msgid'];
    }
}
