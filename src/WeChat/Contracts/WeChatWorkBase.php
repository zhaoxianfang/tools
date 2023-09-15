<?php

namespace zxf\WeChat\Contracts;

use Exception;

/**
 * 企业微信基础类
 */
class WeChatWorkBase extends WeChatBase
{
    // 微信请求地址
    protected $urlBase = "https://qyapi.weixin.qq.com/API_URL?ACCESS_TOKEN";

    /**
     * =======================================================================================
     *       ACCESS_TOKEN 模块  开始
     * =======================================================================================
     */

    /**
     * 去企业微信请求 access_token 参数
     *
     * @link https://developer.work.weixin.qq.com/document/path/91039
     *
     * @return void
     * @throws Exception
     */
    public function requestToken(): void
    {
        $this->useToken = false;
        $url            = $this->parseUrl("cgi-bin/gettoken", [
            "grant_type" => "client_credential",
            "appid"      => $this->config["appid"],
            "secret"     => $this->config["secret"],
        ]);
        $this->useToken = true;

        $res = $this->http->get($url, "json");

        if (isset($res["errcode"]) && $res["errcode"] > 0) {
            $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }

        if (!empty($res["access_token"])) {
            $this->accessToken = $res["access_token"];
            $expiresIn         = (!empty($res["expires_in"]) && $res["expires_in"] > 0) ? $res["expires_in"] : 7100;
            // 缓存token
            $this->setAccessToken($res["access_token"], (int)$expiresIn);
        } else {
            $this->accessToken = "";
            $this->delAccessToken();
        }
    }

    /**
     * =======================================================================================
     *       ACCESS_TOKEN 模块  结束
     * =======================================================================================
     */
}
