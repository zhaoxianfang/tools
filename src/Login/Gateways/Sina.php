<?php

namespace zxf\Login\Gateways;

use Exception;
use zxf\Login\Constants\ConstCode;
use zxf\Login\Contracts\Gateway;

/**
 * Class Sina
 */
class Sina extends Gateway
{
    const API_BASE = 'https://api.weibo.com/2/';

    protected $AuthorizeURL = 'https://api.weibo.com/oauth2/authorize';

    protected $AccessTokenURL = 'https://api.weibo.com/oauth2/access_token';

    protected $AccessGetUidURL = 'https://api.weibo.com/oauth2/get_token_info?access_token=';

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
        ];

        return $this->AuthorizeURL.'?'.http_build_query($params);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function openid()
    {
        if (empty($this->token['access_token'])) {
            $this->getToken();
        }

        if (isset($this->token['openid'])) {
            return $this->token['openid']; // 也就是 uid
        } else {
            $url = $this->AccessGetUidURL.$this->token['access_token'];
            $result = $this->post($url);
            if (isset($result['uid'])) {
                $this->token['openid'] = $result['uid'];

                return $this->token['openid'];
            }

            throw new Exception('没有获取到新浪微博用户ID！');
        }
    }

    /**
     * Description:  获取格式化后的用户信息
     *
     * @return array
     *
     * @throws Exception
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $this->openid(),
            'access_token' => $this->token['access_token'] ?? '',
            'union_id' => $this->openid(),
            'channel' => 'sina', // ConstCode::TYPE_SINA,
            'nickname' => $result['screen_name'],
            'gender_value' => $this->getGender($result['gender']),
            'avatar' => $result['avatar_hd'],
        ];

        return array_merge($result, $userInfo);
    }

    /**
     * Description:  获取原始接口返回的用户信息
     *
     * @return array
     *
     * @throws Exception
     */
    public function getUserInfo()
    {
        if ($this->type == 'app') {// App登录
            if (! isset($_REQUEST['access_token']) || ! isset($_REQUEST['uid'])) {
                throw new Exception('Sina APP登录 需要传输access_token和uid参数! ');
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
            $this->token['openid'] = $_REQUEST['uid'];
        } else {
            /** 获取token */
            $this->getToken();
        }

        return $this->call('users/show.json', ['uid' => $this->openid()]);
    }

    /**
     * Description:  发起请求
     *
     *
     * @return mixed
     */
    private function call($api, array $params = [], string $method = 'GET')
    {
        $method = strtolower($method);

        if (isset($this->token['access_token'])) {
            $params['access_token'] = $this->token['access_token'];
        }

        return $this->$method(self::API_BASE.$api, $params);
    }

    /**
     * Description:  根据第三方授权页面样式切换跳转地址
     */
    private function switchAccessTokenURL()
    {
        if ($this->display == 'mobile') {
            $this->AuthorizeURL = 'https://open.weibo.cn/oauth2/authorize';
        }
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function parseToken($data)
    {
        // $data = json_decode($data, true);
        if (isset($data['access_token'])) {
            $data['openid'] = $data['uid'];
            unset($data['uid']);

            return $data;
        } else {
            throw new Exception("获取新浪微博ACCESS_TOKEN出错：{$data['error']}");
        }
    }

    /**
     * 第三方分享到微博
     *
     * @return mixed
     */
    public function statusesShare($data)
    {
        return $this->call('statuses/share.json', json_encode($data), 'POST');
    }
}
