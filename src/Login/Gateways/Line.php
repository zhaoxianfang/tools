<?php

namespace zxf\Login\Gateways;

use Exception;
use zxf\Login\Constants\ConstCode;
use zxf\Login\Contracts\Gateway;
use zxf\Login\Helper\Str;

/**
 * Class Line
 *
 * TODO: 未验证
 */
class Line extends Gateway
{
    const API_BASE = 'https://api.line.me/v2/';

    protected $AuthorizeURL = 'https://access.line.me/oauth2/v2.1/authorize';

    protected $AccessTokenURL = 'https://api.line.me/oauth2/v2.1/token';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        // 存储state
        $this->saveState();
        // 登录参数
        $params = [
            'response_type' => $this->config['response_type'],
            'client_id' => $this->config['app_id'],
            'redirect_uri' => $this->config['callback'],
            'scope' => $this->config['scope'],
            'state' => $this->config['state'] ?: Str::random(),
        ];

        return $this->AuthorizeURL.'?'.http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $result = $this->getUserInfo();

        return $result['userId'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $result['userId'],
            'access_token' => $this->token['access_token'] ?? '',
            'union_id' => $result['userId'],
            'channel' => ConstCode::TYPE_LINE,
            'nickname' => $result['displayName'],
            'gender' => ConstCode::GENDER, // line不返回性别信息
            'avatar' => isset($result['pictureUrl']) ? $result['pictureUrl'].'/large' : '',
        ];

        return $userInfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        $this->getToken();

        $data = $this->call('profile', $this->token, 'GET');

        if (isset($data['error'])) {
            throw new Exception($data['error_description']);
        }

        return $data;
    }

    /**
     * 发起请求
     *
     * @param  string  $api
     * @param  array  $params
     * @param  string  $method
     * @return array
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method = strtolower($method);
        $request = [
            'method' => $method,
            'uri' => self::API_BASE.$api,
        ];

        $headers = ['Authorization' => $this->token['token_type'].' '.$this->token['access_token']];

        return $this->$method($request['uri'], $params, $headers);
    }

    /**
     * 解析access_token方法请求后的返回值
     *
     * @param  array  $token  获取access_token的方法的返回值
     * @return mixed
     *
     * @throws Exception
     */
    protected function parseToken(array $token)
    {
        // $token = json_decode($token, true);
        if (isset($token['error'])) {
            throw new Exception($token['error_description']);
        }

        return $token;
    }
}
