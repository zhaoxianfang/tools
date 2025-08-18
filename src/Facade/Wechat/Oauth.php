<?php

namespace zxf\Facade\Wechat;

use zxf\Facade\FacadeBase;
use zxf\Facade\FacadeInterface;

/**
 * 微信网页授权管理
 *
 * @method static mixed getOauthRedirect($redirect_url, $state, $scope) Oauth授权跳转接口 @param string $redirect_url 授权回跳地址 @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）@param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
 * @method static mixed getOauthAccessToken() 通过code获取AccessToken和openid
 * @method static mixed getOauthRefreshToken($refresh_token) 刷新AccessToken并续期
 * @method static mixed checkOauthAccessToken($access_token, $openid) 检验授权凭证(access_token)是否有效 @param string $access_token 网页授权接口调用凭证 @param string $openid 用户的唯一标识
 * @method static mixed getUserInfo($access_token,$openid, $lang)       拉取用户信息(需scope为snsapi_userinfo) @param string $access_token 网页授权接口调用凭证 @param string $openid 用户的唯一标识 @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
 */
class Oauth extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\WeChat\OfficialAccount\Oauth::class;
    }
}
