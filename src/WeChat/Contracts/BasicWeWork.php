<?php


namespace zxf\WeChat\Contracts;

use zxf\WeChat\Server\Common\Tools;
use zxf\WeChat\Server\Common\WeChatBase;

/**
 * 企业微信基础类
 * Class BasicWeWork
 *
 * @package zxf\WeChat\Contracts
 */
class BasicWeWork extends WeChatBase
{
    /**
     * 获取访问 AccessToken
     *
     * @return string
     * @throws \Exception
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function getAccessToken()
    {
        if ($this->access_token) {
            return $this->access_token;
        }
        $ckey = $this->config["appid"] . "_access_token";
        if ($this->access_token = Tools::getCache($ckey)) {
            return $this->access_token;
        }
        list($appid, $secret) = [$this->config["appid"], $this->config["appsecret"]];
        $result = Tools::json2arr(Tools::get("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$appid}&corpsecret={$secret}"));
        if (isset($result["access_token"]) && $result["access_token"]) {
            Tools::setCache($ckey, $result["access_token"], 7000);
        }
        return $this->access_token = $result["access_token"];
    }

}