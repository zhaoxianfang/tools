<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 接口调用频次限制
 * Class Limit
 *
 * @package WeChat
 */
class Limit extends WeChatBase
{

    /**
     * 公众号调用或第三方平台帮公众号调用对公众号的所有api调用（包括第三方帮其调用）次数进行清零
     *
     * @return array
     * @throws Exception
     */
    public function clearQuota()
    {
        return $this->post("cgi-bin/clear_quota", ["appid" => $this->config["appid"]]);
    }

    /**
     * 网络检测
     *
     * @param string $action   执行的检测动作
     * @param string $operator 指定平台从某个运营商进行检测
     *
     * @return array
     * @throws Exception
     */
    public function ping($action = "all", $operator = "DEFAULT")
    {
        return $this->post("cgi-bin/callback/check", ["action" => $action, "check_operator" => $operator]);
    }

    /**
     * 获取微信服务器IP地址
     *
     * @return array
     * @throws Exception
     */
    public function getCallbackIp()
    {
        return $this->get("cgi-bin/getcallbackip");
    }
}