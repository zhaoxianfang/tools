<?php

namespace zxf\login;

use Exception;

use zxf\http\Curl;

// QQ auth 登录
class QqOauth implements Handle
{
    protected $client;
    protected $config;
    protected $authorization_url = 'https://graph.qq.com/oauth2.0/authorize';
    protected $token_url         = 'https://graph.qq.com/oauth2.0/token';
    protected $userinfo_url      = 'https://graph.qq.com/user/get_user_info';
    protected $union_url         = 'https://graph.qq.com/oauth2.0/me';

    public function __construct($config)
    {
        $this->config = [
            'client_id'     => $config['appid'],
            'redirect_uri'  => $config['callbackUrl'],
            'client_secret' => $config['appkey'],
        ];
        $this->client = new Curl();
    }

    public function authorization($stateSys = '')
    {
        $state = base64_encode(json_encode(!empty($stateSys) ? $stateSys : 'null'));
        $state = str_en_code($state, 'en');

        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id"     => $this->config['client_id'],
            "redirect_uri"  => $this->config['redirect_uri'],
            "state"         => $state,
            "scope"         => 'get_user_info',
        );
        session('zxf_login_qq_state', $state);

        return $this->combineURL($this->authorization_url, $keysArr);
    }

    public function getAccessToken()
    {
        if (isset($_GET['access_token'])) {//兼容app授权登陆 dcloud返回access_token;
            return $_GET['access_token'];
        }

        $query = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->config['client_id'],
            "redirect_uri"  => urlencode($this->config['redirect_uri']),
            "client_secret" => $this->config['client_secret'],
            "code"          => $_GET['code'],
        );

        $temp_url = $this->combineURL($this->token_url, $query);
        $res      = $this->client->get($temp_url);

        if (isset($res['access_token'])) {
            return $res['access_token'];
        } else {
            throw new \Exception($res['error_description']);
        }
    }


    public function getUserInfo($access_token = '')
    {
        if (empty($access_token)) {
            $access_token = $this->getAccessToken();
        }

        $openidInfo = $this->getOpenid($access_token);
        $query      = array_filter([
            'openid'             => $openidInfo['openid'],
            'oauth_consumer_key' => $openidInfo['client_id'],
            'access_token'       => $access_token,
        ]);

        $temp_url = $this->combineURL($this->userinfo_url, $query);
        $userinfo = $this->client->get($temp_url);
        if (0 != $userinfo['ret']) {
            throw new \Exception($userinfo['msg']);
        }
        $userinfo['unionid'] = !empty($openidInfo['unionid']) ? $openidInfo['unionid'] : null;
        $userinfo['openid']  = $openidInfo['openid'];
        $userinfo['email']   = $openidInfo['openid'] . '@open.qq.com';
        return $userinfo;
    }

    public function getStateParam()
    {
        $state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : session('zxf_login_qq_state');
        if (!empty($state)) {
            $state = urldecode($state);
            $state = str_replace(' ', '+', $state);
            // 进行解密 验证是否为本站发出的state
            $decodeStr = str_en_code($state, 'de');

            try {
                $userParam = json_decode(base64_decode($decodeStr), true);
            } catch (\Exception $e) {
            }
        } else {
            $userParam = [];
        }
        return $userParam != 'null' ? $userParam : '';
    }

    private function getOpenid($access_token): array
    {
        $keysArr  = array(
            "access_token" => $access_token,
            "unionid"      => 1 // 获取 UnionID
        );
        $temp_url = $this->combineURL($this->union_url, $keysArr);
        return $this->client->get($temp_url);
    }


    /**
     * combineURL
     * 拼接url
     *
     * @param string $baseURL 基于的url
     * @param array  $keysArr 参数列表数组
     *
     * @return string           返回拼接的url
     */
    public function combineURL($baseURL, $keysArr)
    {
        $combined = $baseURL . "?";
        $valueArr = array();

        foreach ($keysArr as $key => $val) {
            $valueArr[] = "$key=$val";
        }

        $keyStr   = implode("&", $valueArr);
        $combined .= ($keyStr);

        return $combined;
    }

}

