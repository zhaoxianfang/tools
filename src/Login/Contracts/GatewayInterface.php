<?php

namespace zxf\Login\Contracts;

/**
 * 所有第三方登录必须支持的接口方法
 */
interface GatewayInterface
{
    /**
     * Description:  得到跳转地址
     *
     * @return mixed
     */
    public function getRedirectUrl();

    /**
     * Description:  获取当前授权用户的openid标识
     *
     * @return mixed
     */
    public function openid();

    /**
     * Description:  获取格式化后的用户信息
     *
     * @return mixed
     */
    public function userInfo();

    /**
     * Description:  获取原始接口返回的用户信息
     *
     * @return mixed
     */
    public function getUserInfo();

    /**
     * 刷新AccessToken续期
     *
     * @return bool
     */
    public function refreshToken(string $refreshToken);

    /**
     * 检验授权凭证AccessToken是否有效.
     *
     * @return bool
     *
     */
    public function validateAccessToken(?string $accessToken = null);
}
