<?php

/*
 * 微信网页授权登录
 *
 * 第一步
 * $wechat = new WechatOauth('default');
 * $url = $wechat->authorization($redirect_uri); //授权回调地址，不传则为默认回调地址
 * 发送重定向指令
 * header("Location: {$url}"); 或者 laravel 中 return redirect()->away($url); 等
 * exit; // 确保重定向后立即停止脚本执行
 *
 *
 * 第二步,在回调地址$redirect_uri中调用
 * $wechat = new WechatOauth('default');
 * $tokenInfo = $wechat->getAccessToken() // 获取到access_token，refresh_token，openid，expires_in，scope 数组信息
 * $user = $wechat->getUserInfo($tokenInfo['access_token'],$tokenInfo['openid']) // 获取用户信息
 */

namespace zxf\Login;

use Exception;
use zxf\Http\Curl;

class WechatOauth implements Handle
{
    protected $client;
    protected $config;
    protected $userInfo;
    protected $driver         = 'default';
    protected $needScanQrCode = false;// 是否为扫码登录，true为网页访问需要微信扫码登录，返回微信登录码url图片地址，false为微信授权登录(逐步调用获取用户信息)


    public function __construct(string $driver = 'default')
    {
        if (!$this->isWechatBrowser()) {
            throw new Exception('请在微信浏览器中打开');
        }
        $config = config('tools_wechat.' . $driver);
        if (function_exists('config') && empty($config)) {
            $config = config('tools_wechat.default') ?? [];
        }
        $this->config         = [
            'appid'        => $config['appid'],
            'secret'       => $config['secret'],
            'redirect_uri' => $config['notify_url'],
        ];
        $this->client         = new Curl();
        $this->driver         = $driver;
        $this->needScanQrCode = false;

    }

    // 判断是否是微信浏览器访问
    private function isWechatBrowser()
    {
        $headers         = getallheaders();
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        //判断是不是微信
        if (str_contains($http_user_agent, 'MicroMessenger') || isset($headers['X-Weixin-From'])) {
            return true;
        }
        return false;
    }

    /**
     * 第一步：用户同意授权，获取code 的 url地址，然后get【跳转】到该地址
     *
     * @param string $redirect_uri 授权后重定向的回调链接地址，会使用 urlEncode 对链接进行处理
     * @param string $scope        应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo
     *                             （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
     * @param string $state        重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     *
     * @return string
     */
    public function authorization(string $redirect_uri = '', string $scope = 'snsapi_userinfo', string $state = 'STATE')
    {
        $appId       = $this->config['appid'];
        $redirectUri = empty($redirect_uri) ? urlencode($this->config['redirect_uri']) : urlencode($redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$redirectUri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }

    /**
     * 第二步：通过code换取网页授权access_token
     *
     * @return array|mixed
     * @throws Exception
     */
    public function getAccessToken(string|null $code = '')
    {
        $code   = empty($code) ? $_GET['code'] : $code;
        $appId  = $this->config['appid'];
        $secret = $this->config['secret'];
        $url    = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$secret}&code={$code}&grant_type=authorization_code";

        $res = $this->client->get($url);

        if (isset($res['errmsg'])) {
            throw new Exception('token获取失败:(code:' . $res['errcode'] . '; msg:' . $res['errmsg'] . ')');
        }
        $this->config = array_merge($this->config, $res);
        // 得到 access_token，refresh_token，openid，expires_in，scope 数组信息
        return $res;

    }

    /**
     * 获取用户信息
     *
     * @param string      $access_token
     * @param string|null $openId
     *
     * @return array|mixed
     * @throws Exception
     */
    public function getUserInfo($access_token = '', string|null $openId = '')
    {
        $access_token = empty($access_token) ? $this->config['access_token'] : $access_token;
        $openId       = empty($openId) ? $this->config['openid'] : $openId;
        // 获取个人信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openId}&lang=zh_CN";

        $userInfo = $this->client->get($url);

        if (isset($userInfo['errmsg'])) {
            throw new Exception('token获取失败:(code:' . $userInfo['errcode'] . '; msg:' . $userInfo['errmsg'] . ')');
        }
        // 得到 headimgurl，nickname，openid，sex 等信息,有些还包含 unionid(只有在用户将公众号绑定到微信开放平台账号后，才会出现该字段)
        $this->userInfo = $userInfo;
        return $userInfo;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     *
     * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openId       用户的唯一标识
     *
     * @return bool
     * @throws Exception
     */
    public function verifyAccessToken(string $access_token = '', string $openId = '')
    {
        $url = "https://api.weixin.qq.com/sns/auth?access_token={$access_token}&openid={$openId}";
        $res = $this->client->get($url);
        // 正确时返回 {"errcode":0,"errmsg":"ok"},错误时返回 {"errcode":40003,"errmsg":"invalid openid"}
        return empty($res['errcode']);
    }

    /**
     * 刷新access_token（如果需要）
     *
     * @param string $refresh_token
     *
     * @return array|mixed
     * @throws Exception
     */
    public function refreshAccessToken(string $refresh_token = '')
    {
        $appId = $this->config['appid'];
        $url   = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$appId}&grant_type=refresh_token&refresh_token={$refresh_token}";
        $res   = $this->client->get($url);
        if (isset($res['errmsg'])) {
            throw new Exception('token获取失败:(code:' . $res['errcode'] . '; msg:' . $res['errmsg'] . ')');
        }
        $this->config = array_merge($this->config, $res);
        // 得到 access_token，refresh_token，openid，expires_in，scope 数组信息
        return $res;
    }

    public function getStateParam()
    {
        // 进行解密 验证是否为本站发出的state
        try {
            $state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : i_session('zxf_login_wechat_state');

            $state = urldecode($state);
            $state = str_replace(' ', '+', $state);
            // $state = urldecode($state);
            $decodeStr      = str_en_code($state, 'de');
            $customizeParam = json_decode(base64_decode($decodeStr), true);
        } catch (Exception $e) {
            $customizeParam = "null";
        }

        return $customizeParam != 'null' ? $customizeParam : '';
    }

}
