<?php

namespace zxf\Login\Contracts;

use zxf\Facade\Curl;
use zxf\Login\Constants\ConstCode;
use zxf\Login\Helper\Str;

/**
 * 所有第三方登录必须继承的抽象类
 */
abstract class Gateway implements GatewayInterface
{
    /**
     * 授权地址
     */
    protected $AuthorizeURL;

    /**
     * 获取token地址
     */
    protected $AccessTokenURL;

    /**
     * 获取token地址
     */
    protected $UserInfoURL;

    /**
     * 配置参数
     */
    protected array $config;

    /**
     * AppId
     */
    protected array $app_id;

    /**
     * AppSecret
     */
    protected string $app_secret;

    /**
     * 接口权限值
     */
    protected $scope;

    /**
     * 回调地址
     *
     * @var string
     */
    protected $callback;

    /**
     * 当前时间戳
     *
     * @var int
     */
    protected $timestamp;

    /**
     * 默认第三方授权页面样式
     *
     * @var string
     */
    protected $display = 'default';

    /**
     * 登录类型：app applets
     *
     * @var bool
     */
    protected $type;

    /**
     * 第三方Token信息
     *
     * @var array
     */
    protected $token = null;

    /**
     * 是否验证回跳地址中的state参数
     *
     * @var bool
     */
    protected $checkState = false;

    /**
     * 第三方返回的userInfo
     *
     * @var array
     */
    protected $userInfo = [];

    /**
     * 格式化的userInfo
     *
     * @var array
     */
    protected $formatUserInfo = [];

    /**
     * Gateway constructor.
     *
     * @throws \Exception
     */
    public function __construct($config)
    {
        if (! $config) {
            throw new \Exception('传入的配置不能为空');
        }
        if (isset($_GET['referer']) && $config['callback']) {
            $config['callback'] .= ((str_contains($config['callback'], '?')) ? '&' : '?').'referer='.$_GET['referer'];
        }
        // 默认参数
        $_config = [
            'app_id' => '',
            'app_secret' => '',
            'callback' => '',
            'response_type' => 'code',
            'grant_type' => 'authorization_code',
            'proxy' => '',
            'state' => '',
            'type' => '',
            'is_sandbox' => false, // 是否是沙箱环境
        ];
        $this->config = array_merge($_config, $config);
        foreach ($this->config as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
        $this->timestamp = time();
    }

    /**
     * Description:  设置授权页面样式
     *
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Description:  设置是否是App
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Description:  强制验证回跳地址中的state参数
     *
     * @return $this
     */
    public function mustCheckState()
    {
        $this->checkState = true;

        return $this;
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 设置token(App登录时)
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * 存储state
     */
    public function saveState()
    {
        if ($this->checkState === true) {
            // 是否开启session
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (empty($this->config['state'])) {
                $this->config['state'] = Str::random(); // 生成随机state
            }
            // 存储到session
            $_SESSION['tinymeng_oauth_state'] = $this->config['state'];
        }
    }

    /**
     * 验证state
     *
     * @throws \Exception
     */
    public function checkState()
    {
        if ($this->checkState === true) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (! isset($_REQUEST['state']) || ! isset($_SESSION['tinymeng_oauth_state']) || $_REQUEST['state'] != $_SESSION['tinymeng_oauth_state']) {
                throw new \Exception('传递的STATE参数不匹配！');
            }
        }
    }

    /**
     * 获取授权后的Code
     *
     * @return string
     */
    public function getCode()
    {
        return isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
    }

    /**
     * Description:  默认获取AccessToken请求参数
     *
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'client_id' => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
            'grant_type' => $this->config['grant_type'],
            'code' => $this->getCode(),
            'redirect_uri' => $this->config['callback'],
        ];

        return $params;
    }

    /**
     * Description:  获取AccessToken
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            /** 验证state参数 */
            $this->checkState();

            /** 获取参数 */
            $params = $this->accessTokenParams();

            /** 获取access_token */
            $token = $this->post($this->AccessTokenURL, $params);
            /** 解析token值(子类实现此方法) */
            $this->token = $this->parseToken($token);
        } else {
            return $this->token;
        }
    }

    /**
     * Description:  执行GET请求操作
     *
     *
     * @return mixed
     */
    protected function get($url, array $params = [], array $headers = [])
    {
        return Curl::setHeader($headers)->setParams($params)->get($url);
    }

    /**
     * Description:  执行POST请求操作
     *
     *
     * @return mixed
     */
    protected function post($url, array $params = [], array $headers = [])
    {
        $headers[] = 'Accept: application/json'; // GitHub需要的header

        return Curl::setHeader($headers)->setParams($params)->post($url);
        // return \tinymeng\tools\HttpRequest::httpPost($url, $params, $headers);
    }

    /**
     * 格式化性别参数
     * M代表男性,F代表女性
     */
    public function getGender($gender)
    {
        return strtolower(substr($gender, 0, 1)) == 'm' ? ConstCode::GENDER_MAN : ConstCode::GENDER_WOMEN;
    }

    /**
     * 刷新AccessToken续期(未实现)
     *
     * @param  string  $refreshToken
     * @return bool
     */
    public function refreshToken($refreshToken)
    {
        return true;
    }

    /**
     * 检验授权凭证AccessToken是否有效(未实现)
     *
     * @param  string  $accessToken
     * @return bool
     */
    public function validateAccessToken($accessToken = null)
    {
        return true;
    }
}
