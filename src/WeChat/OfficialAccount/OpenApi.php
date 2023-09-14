<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * OpenApi 管理
 */
class OpenApi extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 公众号调用或第三方平台帮公众号调用对公众号的所有api调用（包括第三方帮其调用）次数进行清零
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/openApi/clear_quota.html#%E8%AF%B7%E6%B1%82%E5%9C%B0%E5%9D%80
     *
     * @return array
     * @throws Exception
     */
    public function clearQuota()
    {
        return $this->post('cgi-bin/clear_quota', ['appid' => $this->config['appid']]);
    }

    /**
     * 查询openAPI调用quota
     * 本接口用于查询公众号/小程序/第三方平台等接口的每日调用接口的额度以及调用次数。
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/openApi/get_api_quota.html
     *
     * @param string $cgi_path api的请求地址，例如"/cgi-bin/message/custom/send";不要前缀“https://api.weixin.qq.com”
     *                         ，也不要漏了"/",否则都会76003的报错
     *
     * @return mixed
     * @throws Exception
     */
    public function getQuota(string $cgi_path)
    {
        return $this->post('cgi-bin/openapi/quota/get', ['cgi_path' => $cgi_path]);
    }

    /**
     * 查询rid信息
     *
     * @param string $rid 调用接口报错返回的rid
     *
     * @return mixed
     * @throws Exception
     */
    public function getRid(string $rid)
    {
        return $this->post('cgi-bin/openapi/rid/get', ['rid' => $rid]);
    }

    /**
     * 使用AppSecret重置 API 调用次数
     * 本接口用于清空公众号/小程序等接口的每日调用接口次数
     *
     * @Link https://developers.weixin.qq.com/doc/offiaccount/openApi/clearQuotaByAppSecret.html
     *
     * @return mixed
     * @throws Exception
     */
    public function clearQuotaByAppSecret()
    {
        return $this->post('cgi-bin/clear_quota/v2', ['appid' => $this->config['appid']]);
    }

    /**
     * 网络检测
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Network_Detection.html
     *
     * @param string $action   执行的检测动作，允许的值：dns（做域名解析）、ping（做ping检测）、all（dns和ping都做）
     * @param string $operator 指定平台从某个运营商进行检测，允许的值：CHINANET（电信出口）、UNICOM（联通出口）、CAP（腾讯自建出口）、DEFAULT（根据ip来选择运营商）
     *
     * @return array
     * @throws Exception
     */
    public function ping(string $action = 'all', string $operator = 'DEFAULT')
    {
        return $this->post('cgi-bin/callback/check', ['action' => $action, 'check_operator' => $operator]);
    }

    /**
     * 获取微信服务器IP地址
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_the_WeChat_server_IP_address.html
     *
     * @return array
     * @throws Exception
     */
    public function getCallbackIp()
    {
        return $this->get('cgi-bin/getcallbackip');
    }
}