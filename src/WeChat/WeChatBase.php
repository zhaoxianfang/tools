<?php

namespace zxf\WeChat;

use Exception;
use zxf\Facade\Curl;
use zxf\Facade\Request;
use zxf\tools\Cache;
use zxf\tools\DataArray;

class WeChatBase extends WechatCode
{
    // 微信请求地址
    private $urlBase = 'https://api.weixin.qq.com/API_URL?access_token=ACCESS_TOKEN';

    // 接口请求的实际url地址
    protected $url = '';

    //curl 对象
    protected $http = '';

    // 缓存对象
    protected $cache = '';

    /**
     * @var object 对象实例
     */
    protected static $instance;

    // 请求接口时候需要的 access_token
    private $accessToken = '';

    public $type = ''; // mini_program（微信小程序） 、 official_account（微信公众号）、server服务端接入

    // Request 请求对象
    public $request;

    // 小程序配置
    protected $config = [
        'token'          => '',
        'appid'          => '',
        'appsecret'      => '',
        'encodingaeskey' => '',

        'token_callback' => '',

        // 缓存目录配置（可选，需拥有读写权限）
        'cache_path'     => "/cache",
        'type'           => "random",
        'mode'           => 1,

        // 配置商户支付参数（可选，在使用支付功能时需要）
        'mch_id'         => "",
        'mch_key'        => '',
        // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
        'ssl_key'        => '',
        'ssl_cer'        => '',
    ];

    public function __construct(array $config = [])
    {
        $this->initConfig($config);
    }

    public static function instance(array $config = [], $refresh = false)
    {
        if (is_null(self::$instance) || $refresh) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * 初始化配置参数
     */
    public function initConfig(array $config = [])
    {
        $config = !empty($config) ? array_merge($this->config, $config) : $this->config;

        $this->config = new DataArray($config);

        if (empty($this->config['appid'])) {
            throw new Exception("Missing Config -- [appid]");
        }
        if (empty($this->config['appsecret'])) {
            throw new Exception("Missing Config -- [appsecret]");
        }
        if (empty($this->config['token'])) {
            throw new Exception("Missing Config -- [token]");
        }

        $this->request = Request::instance();
        $this->http    = Curl::instance();
        $this->cache   = Cache::instance([
            'cache_path' => !empty($this->config['cache_path']) ? $this->config['cache_path'] : "/cache",
            'type'       => !empty($this->config['cache_type']) ? $this->config['cache_type'] : "random",
            'mode'       => !empty($this->config['cache_mode']) ? $this->config['cache_mode'] : 1,
        ]);

        $this->getAccessToken();
        return $this;
    }

    /**
     * 获取「当前正在调用」的url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function error(string $message = '', int $code = 500)
    {
        throw new Exception(!empty($message) ? $message : '出错啦', $code);
    }

    /**
     * sdk 内部 组转生成 接口请求地址
     *
     * @param string $apiUrl 接口请求地址,$urlBase 的 {$url} 部分
     * @param array  $params 拼接在url中的附加参数
     *
     * @return string
     * @throws Exception
     */
    public function generateRequestUrl(string $apiUrl = '', $params = []): string
    {
        if (empty($apiUrl)) {
            throw new Exception('接口请求地址不能为空');
        }
        if (empty($this->accessToken)) {
            $this->getAccessToken(true);
        }
        $this->url = str_replace(['API_URL', 'ACCESS_TOKEN'], [$apiUrl, $this->accessToken], $this->urlBase);
        if (!empty($params)) {
            $this->url .= '&' . http_build_query($params);
        }
        return $this->url;
    }

    /**
     * 获取 access_token
     *
     * @param bool $refreshToken 是否强刷新token
     *
     * @return string
     * @throws Exception
     */
    public function getAccessToken(bool $refreshToken = false): string
    {
        if ($refreshToken) {
            $this->requestToken();
        }
        if ($this->accessToken) {
            return $this->accessToken;
        }
        if (!$this->accessToken = $this->cache->get($this->config['appid'] . '_access_token')) {
            $this->requestToken();
        }
        return $this->accessToken;
    }

    /**
     * 设置外部接口 AccessToken
     *
     * @param $accessToken
     *
     * @return $this
     * @throws Exception
     */
    public function setAccessToken($accessToken = '')
    {
        if (!is_string($accessToken)) {
            throw new Exception("Invalid AccessToken type, need string.");
        }
        // 缓存token
        $this->cache->set($this->config['appid'] . '_access_token', $accessToken, 7200);
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * 清理删除 AccessToken
     *
     * @return bool
     */
    public function delAccessToken()
    {
        $this->accessToken = '';
        return $this->cache->delete($this->config['appid'] . '_access_token');
    }

    /**
     * 获取配置
     *
     * @return string[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 去微信请求 access_token 参数
     *
     * @return $this
     * @throws Exception
     */
    private function requestToken()
    {
        $this->generateRequestUrl('cgi-bin/token', [
            'grant_type' => 'client_credential',
            'appid'      => $this->config['appid'],
            'appsecret'  => $this->config['appsecret'],
        ]);
        $res = $this->http->get($this->url);

        $this->accessToken = $res['access_token'];
        $expiresIn         = !empty($res['expires_in']) ? $res['expires_in'] : 7200;
        // 缓存token
        $this->cache->set($this->config['appid'] . '_access_token', $this->accessToken, $expiresIn);
        return $this;
    }

    /**
     * 判断是否加载了EasyWeChat
     *
     * @return bool
     */
    public function isEasyWeChat(): bool
    {
        return \class_exists('EasyWeChat\Factory');
    }

    /**
     * 发送 post 请求
     *
     * @param string $url
     * @param array  $data
     * @param string $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function post(string $url = '', array $data = [], $urlParams = [])
    {
        $this->generateRequestUrl($url, $urlParams);

        $result = $this->http->setParams($data)->post($this->url);


        if (isset($result['errcode']) && in_array($result['errcode'], ['40014', '40001', '41001', '42001'])) {
            $this->getAccessToken(true);
            return $this->post($url, $data);
        }
        $result['message'] = $this->getMessage($result['errcode']);
        return $result;
    }

    /**
     *  发送get 请求
     *
     * @param string $url
     * @param array  $data
     * @param string $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $url = '', array $data = [], $urlParams = [])
    {
        $this->generateRequestUrl($url, $urlParams);

        $result = $this->http->setParams($data)->get($this->url);

        if (isset($result['errcode']) && in_array($result['errcode'], ['40014', '40001', '41001', '42001'])) {
            $this->getAccessToken(true);
            return $this->get($url, $data);
        }
        $result['message'] = $this->getMessage($result['errcode']);
        return $result;
    }

    /**
     *  请求上传文件 ,主要用在上传公众号素材或者小程序临时图片
     *
     *  说明：请在调用此方法前调用 $this->generateRequestUrl(URL_NAME) 方法，处理 url,
     *  URL_NAME示例：永久素材cgi-bin/material/add_material、临时素材cgi-bin/media/upload
     *
     * @param string $filePath 文件绝对路径
     * @param string $type     image|voice|thumb|video 小程序只有 image 类型
     *                         图片（image）: 10M，支持bmp/png/jpeg/jpg/gif格式       【公众号、小程序】
     *                         语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式 【公众号】
     *                         视频（video）：10MB，支持MP4格式                        【公众号】
     *                         缩略图（thumb）：64KB，支持 JPG 格式                    【公众号】
     * @param string $videoTitle
     * @param string $videoDescription
     *
     * @return array|bool|mixed|string
     * @throws Exception
     */
    public function upload(string $filePath, string $type = 'image', string $videoTitle = '', string $videoDescription = '')
    {
        $data = [];
        if ($type == 'video') {
            $data['description'] = json_encode(
                [
                    'title'        => $videoTitle,
                    'introduction' => $videoDescription,
                ],
                JSON_UNESCAPED_UNICODE
            );
        }
        $headers = [
            'Content-Disposition' => 'form-data; name="media"; filename="' . basename($filePath) . '"',
        ];
        return $this->http->setHeader($headers)->upload($this->url, $filePath, $data);
    }
}
