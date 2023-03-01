<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\Server\Common\BasicWeChat;
use Exception;

/**
 * 微信网页授权
 * Class Oauth
 *
 * @package WeChat
 */
class Oauth extends BasicWeChat
{

    /**
     * Oauth 授权跳转接口
     *
     * @param string $redirect_url 授权回跳地址
     * @param string $state        为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope        授权类类型(可选值snsapi_base|snsapi_userinfo)
     *
     * @return string
     */
    public function getOauthRedirect($redirect_url, $state = '', $scope = 'snsapi_base')
    {
        $appid        = $this->config['appid'];
        $redirect_uri = urlencode($redirect_url);
        return $this->generateRequestUrl('connect/oauth2/authorize', [
                'appid'         => $appid,
                'redirect_uri'  => $redirect_uri,
                'response_type' => 'code',
                'scope'         => $scope,
                'state'         => $state,
            ]) . '#wechat_redirect';
    }

    /**
     * 通过 code 获取 AccessToken 和 openid
     *
     * @param string $code 授权Code值，不传则取GET参数
     *
     * @return array
     * @throws Exception
     */
    public function getOauthAccessToken($code = '')
    {
        $appid     = $this->config['appid'];
        $appsecret = $this->config['appsecret'];
        $code      = !empty($code) ? $code : (isset($_GET['code']) ? $_GET['code'] : '');

        return $this->get("sns/oauth2/access_token", [], [
            'appid'      => $appid,
            'secret'     => $appsecret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * 刷新AccessToken并续期
     *
     * @param string $refresh_token
     *
     * @return array
     * @throws Exception
     */
    public function getOauthRefreshToken($refresh_token)
    {
        $appid = $this->config['appid'];
        return $this->get("sns/oauth2/refresh_token", [], [
            'appid'         => $appid,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * 检验授权凭证（access_token）是否有效
     *
     * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openid       用户的唯一标识
     *
     * @return array
     * @throws Exception
     */
    public function checkOauthAccessToken($access_token, $openid)
    {
        return $this->get("sns/auth?access_token", [], [
            'access_token' => $access_token,
            'openid'       => $openid,
        ]);
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     *
     * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openid       用户的唯一标识
     * @param string $lang         返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     *
     * @return array
     * @throws Exception
     */
    public function getUserInfo($access_token, $openid, $lang = 'zh_CN')
    {
        return $this->get("sns/userinfo", [], [
            'access_token' => $access_token,
            'openid'       => $openid,
            'lang'         => $lang,
        ]);
    }
}
