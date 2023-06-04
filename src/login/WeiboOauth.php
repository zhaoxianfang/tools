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
            $config = config('ext_auth.sina.default') ?? [];
        }
        $this->config = [
            'client_id'     => $config['wb_akey'],
            'client_secret' => $config['wb_skey'],
            'redirect_uri'  => $config['wb_callback_url'],
        ];
        $this->client = new Curl();
    }

    public function authorization($stateSys = '')
    {
        $state = base64_encode(json_encode(!empty($stateSys) ? $stateSys : 'null'));
        $state = str_en_code($state, 'en');

        $url = 'https://api.weibo.com/oauth2/authorize';

        $query = array_filter([
            'client_id'     => $this->config['client_id'],
            'redirect_uri'  => $this->config['redirect_uri'],
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
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri'  => $this->config['redirect_uri'],
            'code'          => $_GET['code'],
            'grant_type'    => 'authorization_code',
        ]);

        $res = $this->client->setParams($query, 'string')->post($url);

        if (isset($res['error_description'])) {
            throw new \Exception('登录失败，请重试');
        }
        return $res;

    }

    public function getUserInfo($access_token = '')
    {
        $uid = null;
        if (empty($access_token)) {
            $access_token_info = $this->getAccessToken();
            $access_token      = $access_token_info['access_token'];
            $uid               = !empty($access_token_info['uid']) ? $access_token_info['uid'] : null;
        }
        $url = 'https://api.weibo.com/2/users/show.json';

        $uid = empty($uid) ? $this->getUid($access_token) : $uid;

        $query = array_filter([
            'uid'          => $uid,
            'access_token' => $access_token,
        ]);

        $userInfo           = $this->client->setParams($query)->get($url);

        $userInfo['openid'] = $uid;
        !empty($access_token_info) && ($userInfo['isRealName'] = $access_token_info['isRealName']);
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
