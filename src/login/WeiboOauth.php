<?php

/*
 * 微博登录
 */

namespace zxf\login;

use zxf\http\Curl;

class WeiboOauth implements Handle
{
    protected $client;
    protected $config;

    public function __construct(array $config = [])
    {
        if (function_exists('config') && empty($config)) {
            $this->config = config('ext_auth.sina.default') ?? [];
        } else {
            $this->config = [
                'wb_akey'         => $config['wb_akey'],
                'wb_skey'         => $config['wb_skey'],
                'wb_callback_url' => $config['wb_callback_url'],
            ];
        }
        $this->client = new Curl();
    }

    public function authorization($stateSys = '')
    {
        $state = base64_encode(json_encode(!empty($stateSys) ? $stateSys : 'null'));
        $state = str_en_code($state, 'en');

        $url = 'https://api.weibo.com/oauth2/authorize';

        $query = array_filter([
            'client_id'     => $this->config['wb_akey'],
            'redirect_uri'  => $this->config['wb_callback_url'],
            'response_type' => 'code',
            'state'         => $state,
        ]);
        session('zxf_login_weibo_state', $state);

        return $url . '?' . http_build_query($query);
    }

    public function getAccessToken()
    {
        if ('token' == $_GET['code']) {
            return $_GET['access_token'];
        }
        $url = 'https://api.weibo.com/oauth2/access_token';

        $query = array_filter([
            'client_id'     => $this->config['wb_akey'],
            'code'          => $_GET['code'],
            'client_secret' => $this->config['wb_skey'],
            'redirect_uri'  => $this->config['wb_callback_url'],
            'grant_type'    => 'authorization_code',
        ]);

        $res = $this->client->setParams($query)->post($url);
        if (isset($res['error_description'])) {
            throw new \Exception('登录失败，请重试');
        }
        return $res['access_token'];

    }

    public function getUserInfo($access_token = '')
    {
        if (empty($access_token)) {
            $access_token = $this->getAccessToken();
        }
        $url = 'https://api.weibo.com/2/users/show.json';

        $uid   = $this->getUid($access_token);
        $query = array_filter([
            'uid'          => $uid,
            'access_token' => $access_token,
        ]);

        $userInfo           = $this->client->setParams($query)->get($url);
        $userInfo['openid'] = $uid;
        return $userInfo;
    }

    public function getStateParam()
    {
        // 进行解密 验证是否为本站发出的state
        try {
            $state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : session('zxf_login_weibo_state');

            $state = urldecode($state);
            $state = str_replace(' ', '+', $state);
            // $state = urldecode($state);
            $decodeStr      = str_en_code($state, 'de');
            $customizeParam = json_decode(base64_decode($decodeStr), true);
        } catch (\Exception $e) {
            $customizeParam = "null";
        }

        return $customizeParam != 'null' ? $customizeParam : '';
    }

    public function getUid($access_token)
    {
        $url    = 'https://api.weibo.com/oauth2/get_token_info?access_token=' . $access_token;
        $result = $this->client->post($url);
        return $result['uid'];
    }
}
