<?php

namespace zxf\Wechat\Menu;

use zxf\Wechat\Bridge\Http;
use zxf\Wechat\Wechat\AccessToken;

class Query
{
    /**
     * 接口地址
     */
    const QUERY_URL = 'https://api.weixin.qq.com/cgi-bin/menu/get';

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
     * 获取响应结果.
     */
    public function doQuery()
    {
        $response = Http::request('GET', static::QUERY_URL)
            ->withAccessToken($this->accessToken)
            ->send();

        if (0 != $response['errcode']) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}
