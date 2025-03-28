<?php

/**
 * naver开放平台  https://developers.naver.com/
 * api接口文档
 *      https://developers.naver.com/docs/common/openapiguide/
 * 申请的时候请选择
 */

namespace zxf\Login\Gateways;

use Exception;
use zxf\Login\Constants\ConstCode;
use zxf\Login\Contracts\Gateway;
use zxf\Login\Helper\Str;

/**
 * Class Naver
 *
 * TODO: 未验证
 */
class Naver extends Gateway
{
    const API_BASE = 'https://openapi.naver.com/v1/';

    protected $AuthorizeURL = 'https://nid.naver.com/oauth2.0/authorize';

    protected $AccessTokenURL = 'https://nid.naver.com/oauth2.0/token';

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
            'open_id' => $this->token['access_token'],
            'union_id' => $result['id'],
            'channel' => ConstCode::TYPE_NAVER,
            'email' => $result['email'] ?? '',
            'nickname' => $result['nickname'] ?? '',
            'gender' => isset($result['gender']) ? $this->getGender($result['gender']) : ConstCode::GENDER,
            'avatar' => $result['profile_image'] ?? '',
        ];

        return $userInfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        if ($this->type == 'app') {// App登录
            if (! isset($_REQUEST['access_token'])) {
                throw new Exception('Naver APP登录 需要传输access_token参数! ');
            }
            $this->token['token_type'] = 'Bearer';
            $this->token['access_token'] = $_REQUEST['access_token'];
        } else {
            $this->getToken();
        }
        $data = $this->call('nid/me', $this->token, 'GET');
        if (isset($data['error'])) {
            throw new Exception($data['error_description']);
        }

        return $data['response'];
    }

    /**
     * 发起请求
     *
     * @param string $api
     * @param array  $params
     * @param string $method
     *
     * @return array
     */
    private function call(string $api, array $params = [], string $method = 'GET')
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
     * @return array
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
