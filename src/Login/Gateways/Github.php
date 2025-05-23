<?php

/**
 * Github  https://github.com/settings/developers
 * api接口文档
 *      https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/
 *
 * TODO: 未验证
 */

namespace zxf\Login\Gateways;

use zxf\Login\Constants\ConstCode;
use zxf\Login\Contracts\Gateway;

class Github extends Gateway
{
    const API_BASE = 'https://api.github.com/';

    protected $AuthorizeURL = 'https://github.com/login/oauth/authorize';

    protected $AccessTokenURL = 'https://github.com/login/oauth/access_token';

    protected $UserInfoURL = 'user';

    /**
     * @throws \Exception
     */
    public function __construct(string|array|null $config = [])
    {
        parent::__construct($config);
        $this->UserInfoURL = static::API_BASE.$this->UserInfoURL;
    }

    /**
     * Description:  得到跳转地址
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        // 存储state
        $this->saveState();
        // 登录参数
        $this->switchAccessTokenURL();
        $params = [
            'client_id' => $this->config['app_id'],
            'redirect_uri' => $this->config['callback'],
            'scope' => $this->config['scope'],
            'state' => $this->config['state'],
            'display' => $this->display,
            'allow_signup' => true, // 是否会在OAuth流程中为未经身份验证的用户提供注册GitHub的选项。默认是true。false在策略禁止注册的情况下使用。
        ];

        return $this->AuthorizeURL.'?'.http_build_query($params);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function openid()
    {
        $this->getToken();

        if (isset($this->token['openid'])) {
            return $this->token['openid'];
        } else {
            throw new \Exception('没有获取到新浪微博用户ID！');
        }
    }

    /**
     * Description:  获取格式化后的用户信息
     *
     * @return array
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $result['id'],
            'access_token' => $this->token['access_token'],
            'union_id' => $result['id'],
            'channel' => ConstCode::TYPE_GITHUB,
            'nickname' => $result['name'],
            'username' => $result['login'],
            'avatar' => $result['avatar_url'],
            'email' => $result['email'],
            'sign' => $result['bio'],
            'gender' => ConstCode::GENDER,
        ];

        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getUserInfo()
    {
        $this->getToken();

        $headers = [
            'User-Agent: '.$this->config['application_name'],
            'UserModel-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 ',
            'Authorization: token '.$this->token['access_token'],
            'Accept: application/json',
        ];
        $response = $this->get($this->UserInfoURL, [], $headers);
        $data = json_decode($response, true);
        if (! empty($data['error'])) {
            throw new \Exception($data['error']);
        }

        return $data;
    }

    /**
     * Description:  根据第三方授权页面样式切换跳转地址
     */
    private function switchAccessTokenURL()
    {
        if ($this->display == 'mobile') {
        }
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function parseToken($data)
    {
        // $data = json_decode($data, true);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取GitHub ACCESS_TOKEN出错：{$data['error']}");
        }
    }
}
