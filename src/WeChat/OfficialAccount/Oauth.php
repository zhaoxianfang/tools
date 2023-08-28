<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信网页授权
 */
class Oauth extends WeChatBase
{
    public $useToken = false;

    /**
     * Oauth 授权跳转接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#0
     *
     * @param string $redirect_url 授权回跳地址
     * @param string $state        为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope        授权类类型(可选值snsapi_base|snsapi_userinfo)
     *
     * @return string
     */
    public function getOauthRedirect(string $redirect_url, string $state = '', string $scope = 'snsapi_base')
    {
        $appid        = $this->config['appid'];
        $redirect_uri = urlencode($redirect_url);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * 通过 code 获取 AccessToken 和 openid
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#1
     *
     * @param string $code 授权Code值，不传则取GET参数
     *
     * @return array
     * @throws Exception
     */
    public function getOauthAccessToken(string $code = '')
    {
        $appid     = $this->config['appid'];
        $appsecret = $this->config['appsecret'];
        $code      = $code ?? ($_GET['code'] ?? '');
        return $this->get('sns/oauth2/access_token', [], ['code' => $code, 'grant_type' => 'authorization_code', 'appid' => $appid, 'secret' => $appsecret]);
    }

    /**
     * 刷新AccessToken并续期
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#2
     *
     * @param string $refresh_token
     *
     * @return array
     * @throws Exception
     */
    public function getOauthRefreshToken(string $refresh_token)
    {
        return $this->get('sns/oauth2/refresh_token', [], ['appid' => $this->config['appid'], 'grant_type' => 'refresh_token', 'refresh_token' => $refresh_token]);
    }

    /**
     * 检验授权凭证（access_token）是否有效
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#4
     *
     * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openid       用户的唯一标识
     *
     * @return array
     * @throws Exception
     */
    public function checkOauthAccessToken(string $access_token, string $openid)
    {
        return $this->get('sns/auth', [], ['access_token' => $access_token, 'openid' => $openid]);
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
     *
     * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openid       用户的唯一标识
     * @param string $lang         返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     *
     * @return array
     * @throws Exception
     */
    public function getUserInfo(string $access_token, string $openid, string $lang = 'zh_CN')
    {
        return $this->get('sns/userinfo', [], ['access_token' => $access_token, 'openid' => $openid, 'lang' => $lang]);
    }

}