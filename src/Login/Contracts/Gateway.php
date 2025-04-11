<?php

namespace zxf\Login\Contracts;

use Exception;
use zxf\Facade\Curl;
use zxf\Login\Constants\ConstCode;

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
    protected string $app_id;

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
     * 通过回跳state解析出传入的回调参数
     *      通过mustCheckState(string|array $callbackState = '')设置需要验证state时
     *      可传入一个 string|array 类型的参数并进行本地session存储，当第三方授权回调后调
     *      用 checkState() 方法验证state成功后会解析并返回传入的$callbackState的原始值
     */
    protected string|array $callbackState = '';

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
     * 第三方OAuth的名称(小写)，例如 qq,sina,...
     */
    protected string $oAuthName = '';

    /**
     * Gateway constructor.
     *
     * @throws Exception
     */
    public function __construct(string|array|null $config = [])
    {
        $this->oAuthName = strtolower(basename(str_replace('\\', '/', get_class($this))));

        // 场景: eg: default、mobile ... [由你的 tools_oauth 配置决定]
        $scene = (is_string($config) || ! empty($config)) ? $config : 'default';

        $driverConfig = [];
        if (empty($config) || is_string($config)) {
            if (function_exists('config')) {
                $driverConfig = config("tools_oauth.{$this->oAuthName}.{$scene}") ?? [];
            }
        } else {
            $driverConfig = $config;
        }
        empty($driverConfig) && throw new Exception("[$this->oAuthName] 的配置不能为空");

        if (isset($_GET['referer']) && $driverConfig['callback']) {
            $driverConfig['callback'] .= ((str_contains($driverConfig['callback'], '?')) ? '&' : '?').'referer='.$_GET['referer'];
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
        $this->config = ! empty($driverConfig) ? array_replace_recursive($_config, $driverConfig) : $_config;

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
     * @param  string|array  $callbackState  需要验证state时可设置此参数进行本地session存储，
     *                                       第三方授权回调后可在 checkState() 方法验证state
     *                                       成功后会解析并返回传入的$callbackState的原始值
     * @return $this
     */
    public function mustCheckState(string|array $callbackState = '')
    {
        $this->checkState = true;
        ! empty($callbackState) && $this->callbackState = $callbackState;

        return $this;
    }

    /**
     * 获取配置信息
     */
    public function getConfig(): array
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
            $this->callbackState = $this->callbackState ?? '';

            $stateCode = base64_encode(json_encode($this->callbackState));
            $this->config['state'] = str_en_code($stateCode, 'en');

            // 存储到本地 session 文件保存
            i_session('tools_login_oauth_state', $this->config['state']);
        }
    }

    /**
     * 验证state
     *
     * @throws Exception
     */
    public function checkState()
    {
        if ($this->checkState === true) {
            $state = ! empty($_REQUEST['state']) ? $_REQUEST['state'] : i_session('tools_login_oauth_state');
            if (empty($state)) {
                throw new Exception('非法请求:未携带STATE参数！');
            }
            $state = urldecode($state);
            $state = str_replace(' ', '+', $state);
            // 进行解密 验证是否为本站发出的state
            $decodeStr = str_en_code($state, 'de');

            try {
                $this->callbackState = json_decode(base64_decode($decodeStr), true);
            } catch (\Exception $e) {
                $this->callbackState = '';
            }
            return $this->callbackState != 'null' ? $this->callbackState : '';
        }
        return '';
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
            // 不同的OAuth类型不同，获取access_token的请求方式不同,例如：qq 使用 get请求，sina 使用 post请求
            $method = match ($this->oAuthName) {
                // 'qq', 'name' => 'get',
                'qq' => 'get',
                'sina' => 'post',
                default => null,
            };

            if ($this->oAuthName == 'qq') {
                $token = $this->get($this->AccessTokenURL, $params);
            } elseif ($this->oAuthName == 'sina') {
                $token = $this->post($this->AccessTokenURL, $params, [], 'string');
            } else {
                // 默认使用get请求
                $token = $this->get($this->AccessTokenURL, $params);
            }

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
    protected function get($url, array $params = [], array $headers = [], string $data_type = 'array')
    {
        return Curl::setParams($params, $data_type)->inject(function ($http, $ch) use ($headers) {
            ! empty($headers) ? $http->setHeader($headers, false) : $http->cleanHeader();
        })->get($url);
    }

    /**
     * Description:  执行POST请求操作
     *
     *
     * @return mixed
     */
    protected function post($url, array $params = [], array $headers = [], string $data_type = 'array')
    {
        if ($this->oAuthName = 'github') {
            $headers[] = 'Accept: application/json'; // GitHub需要的header
        }

        return Curl::setParams($params, $data_type)->inject(function ($http, $ch) use ($headers) {
            ! empty($headers) ? $http->setHeader($headers, false) : $http->cleanHeader();
        })->post($url);
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
