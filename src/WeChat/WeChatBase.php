<?php

namespace zxf\WeChat;

use Exception;
use zxf\req\Curl;
use zxf\tools\Cache;

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

    public $type = ''; // mini_program（微信小程序） 或者 official_account（微信公众号）

    // 小程序配置
    protected $config = [
        'app_id'    => '',
        'secret'    => '',
        // 缓存access_token
        'cache_dir' => "/cache",
        'type'      => "random",
        'mode'      => 1,
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
        $this->config = !empty($config) ? array_merge($this->config, $config) : $this->config;
        if (empty($this->config['app_id']) || empty($this->config['secret'])) {
            throw new Exception('确少微信小程序配置参数:app_id或secret');
        }

        $this->http  = Curl::instance();
        $this->cache = Cache::instance([
            'cache_dir' => !empty($this->config['cache_dir']) ? $this->config['cache_dir'] : "/cache",
            'type'      => !empty($this->config['cache_type']) ? $this->config['cache_type'] : "random",
            'mode'      => !empty($this->config['cache_mode']) ? $this->config['cache_mode'] : 1,
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
        if (!$this->accessToken = $this->cache->get('wechat_access_token')) {
            $this->requestToken();
        }
        return $this->accessToken;
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
            'appid'      => $this->config['app_id'],
            'secret'     => $this->config['secret'],
        ]);
        $res = $this->http->get($this->url);

        $this->accessToken = $res['access_token'];
        $expiresIn         = !empty($res['expires_in']) ? $res['expires_in'] : 7200;
        // 缓存token
        $this->cache->set('wechat_access_token', $this->accessToken, $expiresIn);
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

        if (isset($result['errcode']) && $result['errcode'] == '40001') {
            $this->getAccessToken(true);
            return $this->post($url, $data);
        }
        $result['message'] = $this->getCode($result['errcode']);
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

        if (isset($result['errcode']) && $result['errcode'] == '40001') {
            $this->getAccessToken(true);
            return $this->get($url, $data);
        }
        $result['message'] = $this->getCode($result['errcode']);
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
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception(sprintf('文件不存在或者不可读: "%s"', $filePath));
        }

        if (class_exists('\CURLFile')) {
            $data = ['media' => new \CURLFile(realpath($filePath))];
        } else {
            $data = ['media' => '@' . realpath($filePath)];//<=5.5
        }

        $headers = [
            'Content-Disposition' => 'form-data; name="media"; filename="' . basename($filePath) . '"',
        ];

        if ($type == 'video') {
            $data['description'] = json_encode(
                [
                    'title'        => $videoTitle,
                    'introduction' => $videoDescription,
                ],
                JSON_UNESCAPED_UNICODE
            );
        }

        return $this->curlPost($this->url, $data, $headers);// 成功时候返回数据 包含media_id 、url，失败时返回数据包含 errcode 和 message
    }

    /**
     * 上传文件素材 时候使用的请求方法
     *
     * @param $url
     * @param $data
     * @param $header
     *
     * @return array|bool|mixed|string
     * @throws Exception
     */
    private function curlPost($url, $data, $header = [])
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if (is_array($header) && !empty($header)) {
                $set_head = array();
                foreach ($header as $k => $v) {
                    $set_head[] = "$k:$v";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $set_head);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
            // print_r(curl_getinfo($ch));
            curl_close($ch);
            $info = array();
            if ($response) {
                $info = $response;
                try {
                    $info = json_decode($response, true);
                } catch (Exception $e) {
                }
            }
            return $info;
        } else {
            throw new Exception('不支持CURL功能.');
        }
    }
}
