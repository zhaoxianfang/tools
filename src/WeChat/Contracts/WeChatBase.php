<?php

namespace zxf\WeChat\Contracts;

use Exception;
use zxf\Facade\Curl;
use zxf\Facade\Request;
use zxf\Tools\Cache;
use zxf\Tools\DataArray;

abstract class WeChatBase extends WechatCode
{
    // 微信请求地址
    protected $urlBase = "https://api.weixin.qq.com/API_URL?ACCESS_TOKEN";

    // 已经解析好的接口请求url地址
    protected string $url = "";
    // 未解析的原始url, $urlBase 中的 API_URL
    protected string $originalUrl = "";

    //curl 对象
    protected $http;

    // 缓存对象
    protected $cache;

    /**
     * @var object 对象实例数组
     */
    protected static $instance;

    // 请求接口时候需要的 access_token
    private string|null $accessToken = "";

    // 接口url中是否使用 $accessToken 参数
    public bool $useToken = true;

    // Request 请求对象
    public $request;

    // 需要重新获取token请求的状态码
    private array $tryAgainCode = ["40014", "40001", "41001", "42001"];

    // 当前重请求次数
    private int $tryAgainNum = 0;

    // 允许最大重试次数
    private int $tryAgainMax = 2;

    // 微信配置
    protected array|DataArray $config = [
        "token"      => "",
        "appid"      => "",
        "secret"     => "",
        "aes_key"    => "",
        "notify_url" => "",

        // 缓存目录配置（可选，需拥有读写权限）
        'cache_path' => '',
    ];

    /**
     * 是否在构造函数中初始化配置
     * 有少数继承本基类的类在构造函数中需要禁止初始化配置，改为手动调用初始化 eg: parent::instance('work');
     *
     * @var bool 默认为true
     */
    protected bool $defaultInit = true;

    public function __construct(string $driver = 'default')
    {
        if ($this->defaultInit) {
            return $this->init($driver);
        }
    }

    /**
     * 静态创建对象
     *
     * @param string $driver
     *
     * @return static
     */
    public static function instance(string $driver = 'default'): self
    {
        $key = md5(get_called_class() . $driver);
        if (isset(self::$instance[$key]) && !empty(self::$instance[$key])) {
            return self::$instance[$key];
        }
        return self::$instance[$key] = new static($driver);
    }

    /**
     * 初始化配置参数
     */
    private function init(string $driver = 'default'): bool|static
    {
        $config = config('tools_wechat.' . $driver);

        $this->request = Request::instance();
        $this->http    = Curl::instance();
        $this->cache   = Cache::instance();

        if (empty($config) && empty($config = $this->cache->get('lately_wechat_config', []))) {
            return false;
        }

        $this->config = new DataArray($config + $this->config);

        // 企业微信使用 corp_id，其他的使用 appid
        if (empty($this->config["appid"]) && empty($this->config["corp_id"])) {
            $this->error("Missing Config -- [appid/corpid]");
        }
        if (empty($this->config["secret"])) {
            $this->error("Missing Config -- [secret]");
        }
        if (empty($this->config["token"])) {
            $this->error("Missing Config -- [token]");
        }

        empty($this->config['cache_path']) || $this->cache->setCacheDir($this->config['cache_path']);

        $this->url         = "";
        $this->accessToken = "";
        $this->tryAgainNum = 0;
        // 缓存最近一次的配置
        $this->cache->set('lately_wechat_config', $this->config->toArray());

        return $this;
    }

    /**
     * 添加需要重新获取token请求的状态码
     *
     * @param array $code 状态码 eg:[40014]
     *
     * @return $this
     */
    public function addTryAgainCode(array $code = [])
    {
        if (!empty($code)) {
            $this->tryAgainCode = array_unique(array_merge($this->tryAgainCode, $code));
        }
        return $this;
    }

    public function setDriver(string $driver = 'default')
    {
        $this->init($driver);
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
     * 获取配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }


    /**
     * 请求接口中是否使用 access_token 参数
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function enableToken(bool $bool = true): self
    {
        $this->useToken = $bool;
        return $this;
    }

    /**
     * 获取 API_URL
     */
    public function getApiUrl()
    {
        return $this->originalUrl;
    }

    /**
     * 设置原始请求的 API_URL,http 开头的不设置值
     *
     * @param string $url
     *
     * @return WeChatBase
     */
    public function setApiUrl(string $url = "")
    {
        if (!str_starts_with($url, "http")) {
            $this->originalUrl = $url;
        }
        return $this;
    }

    /**
     * 判断是否加载了EasyWeChat
     *
     * @return bool
     */
    public function isEasyWeChat(): bool
    {
        return \class_exists("EasyWeChat\Factory");
    }

    /**
     * 抛出异常
     *
     * @param string $message
     * @param int    $code
     *
     * @return mixed
     * @throws Exception
     */
    public function error(string $message = "", int $code = 500)
    {
        throw new Exception(!empty($message) ? $message : "出错啦", $code);
    }

    /**
     * =======================================================================================
     *       ACCESS_TOKEN 模块  开始
     * =======================================================================================
     */

    /**
     * 去微信请求 access_token 参数
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getAccessToken.html
     *
     * @return void
     * @throws Exception
     */
    public function requestToken(): void
    {
        $this->useToken = false;
        $url            = $this->parseUrl("cgi-bin/token", [
            "grant_type" => "client_credential",
            "appid"      => $this->config["appid"],
            "secret"     => $this->config["secret"],
        ]);
        $this->useToken = true;

        $res = $this->http->get($url, "json");

        if (isset($res["errcode"]) && $res["errcode"] > 0) {
            $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }

        if (!empty($res["access_token"])) {
            $this->accessToken = $res["access_token"];
            $expiresIn         = (!empty($res["expires_in"]) && $res["expires_in"] > 0) ? $res["expires_in"] : 7100;
            // 缓存token
            $this->setAccessToken($res["access_token"], (int)$expiresIn);
        } else {
            $this->accessToken = "";
            $this->delAccessToken();
        }
    }

    /**
     * 获取稳定版接口调用凭据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getStableAccessToken.html
     */
    public function getStableAccessToken()
    {
        $this->useToken = false;
        $url            = $this->parseUrl("cgi-bin/stable_token", [
            "grant_type"    => "client_credential",
            "appid"         => $this->config["appid"],
            "secret"        => $this->config["secret"],
            "force_refresh" => true, // 默认使用 false。1. force_refresh = false 时为普通调用模式，access_token 有效期内重复调用该接口不会更新 access_token；2. 当force_refresh = true 时为强制刷新模式，会导致上次获取的 access_token 失效，并返回新的 access_token
        ]);
        $this->useToken = true;

        $res = $this->http->post($url, "json");
        if (isset($res["errcode"]) && $res["errcode"] > 0) {
            $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }

        if (!empty($res["access_token"])) {
            $this->accessToken = $res["access_token"];
            $expiresIn         = (!empty($res["expires_in"]) && $res["expires_in"] > 0) ? $res["expires_in"] : 7100;
            // 缓存token
            $this->setAccessToken($res["access_token"], (int)$expiresIn);
        } else {
            $this->accessToken = "";
            $this->delAccessToken();
        }
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
        if (!empty($this->accessToken) || !empty($this->accessToken = $this->cache->get($this->setCacheTokenKey()))) {
            return $this->accessToken;
        }
        $this->requestToken();
        return $this->accessToken;
    }

    /**
     * 外部接口 设置 AccessToken
     *
     * @param string $accessToken accessToken字符串
     * @param int    $expiresIn   过期时间，标准时间为7200，请求中可能有消耗，此处默认7100
     *
     * @return $this
     * @throws Exception
     */
    public function setAccessToken(string $accessToken = "", int $expiresIn = 7100): self
    {
        if (!is_string($accessToken) || empty($accessToken)) {
            return $this->error("Invalid AccessToken type, need string.");
        }
        // 缓存token
        $this->cache->set($this->setCacheTokenKey(), $accessToken, $expiresIn);
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * 清理删除 AccessToken
     *
     * @return bool
     */
    public function delAccessToken(): bool
    {
        $this->accessToken = "";
        return $this->cache->delete($this->setCacheTokenKey());
    }

    private function setCacheTokenKey(): string
    {
        $keyPrefix = $this->config["appid"];
        if (!empty($this->config["corp_id"])) {
            $keyPrefix = $this->config["corp_id"];
        }
        return $keyPrefix . "_access_token";
    }

    /**
     * =======================================================================================
     *       ACCESS_TOKEN 模块  结束
     * =======================================================================================
     */


    /**
     * 解析请求的url
     *
     * @param string     $apiUrl   接口请求地址 例如 https://api.weixin.qq.com/card/create?access_token=ACCESS_TOKEN 中的
     *                             card/create
     * @param array|null $params   拼接在url中的附加参数
     *
     * @return string
     * @throws Exception
     */
    public function parseUrl(string $apiUrl = "", ?array $params = []): string
    {
        if (empty($apiUrl)) {
            return $this->error("接口请求地址不能为空");
        }

        $this->setApiUrl($apiUrl);

        $baseUrl = str_starts_with($apiUrl, "http") ? $apiUrl : $this->urlBase;

        // 是否需要拼接 access_token
        $token = !$this->useToken ? '' : "access_token=" . $this->getAccessToken();

        $url = str_replace(["API_URL", "ACCESS_TOKEN"], [$apiUrl, $token], $baseUrl);

        $urlQuery = !empty($params) ? http_build_query($params) : "";

        if (!empty($urlQuery) && is_bool(stripos($url, $urlQuery))) {
            $url = trim($url, '?');
            $url .= ((stripos($url, "?")) ? "&" : "?") . $urlQuery;
        }

        return $url;
    }

    /**
     * 发送 post 请求
     *
     * @param string       $url
     * @param array|string $data
     * @param array        $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function post(string $url = "", array|string $data = [], array $urlParams = [])
    {
        $this->url = $this->parseUrl($url, $urlParams);
        $result    = $this->http->setParams($data, 'json')->post($this->url);
        return $this->getCurlResult($result, 'post', $url, $data, $urlParams);
    }

    /**
     *  发送get 请求
     *
     * @param string       $url
     * @param array|string $data
     * @param array        $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $url = "", array|string $data = [], array $urlParams = [])
    {
        $this->url = $this->parseUrl($url, $urlParams);
        $result    = $this->http->setParams($data, 'string')->get($this->url);
        return $this->getCurlResult($result, 'get', $url, $data, $urlParams);
    }

    /**
     * 获取curl请求结果,如果请求失败,则尝试重新请求
     *
     * @param              $curlResult
     * @param string       $funcStr 请求方法 get/post
     * @param string       $url
     * @param array|string $data
     * @param array        $urlParams
     * @param mixed        ...$args
     *
     * @return mixed
     * @throws Exception
     */
    private function getCurlResult($curlResult, string $funcStr, string $url = "", array|string $data = [], array $urlParams = [], ...$args)
    {
        if (isset($curlResult["errcode"]) && $curlResult["errcode"] > 0) {
            if (in_array($curlResult["errcode"], $this->tryAgainCode)) {
                if ($this->tryAgainNum > $this->tryAgainMax) {
                    $this->tryAgainNum = 0;
                    return $this->error("尝试多次请求都失败了!", $curlResult["errcode"]);
                }
                $this->tryAgainNum++;
                $this->getAccessToken(true);
                return $this->$funcStr($url, $data, $urlParams, ...$args);
            }
            $curlResult["message"] = $this->getMessage($curlResult["errcode"]);
        }
        $this->tryAgainNum = 0;
        return $curlResult;
    }

    /**
     * 直接调用http上传文件(不包含视频文件)
     *
     * @param string     $url       请求地址
     * @param string     $filePath  文件绝对路径
     * @param array|null $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function httpUpload(string $url = "", string $filePath = '', ?array $urlParams = [])
    {
        $this->url = $this->parseUrl($url, $urlParams);
        return $this->http->upload($this->url, $filePath);
    }


    /**
     *  请求上传【素材】文件 ,主要用在上传公众号素材或者小程序临时图片
     *
     * @param string $mediaType 上传类型：10：小程序临时图片，20：公众号临时素材，21：公众号永久素材
     * @param string $filePath  文件绝对路径
     * @param string $type      image|voice|thumb|video 小程序只有 image 类型
     *                          图片（image）: 10M，支持bmp/png/jpeg/jpg/gif格式       【公众号、小程序】
     *                          语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式 【公众号】
     *                          视频（video）：10MB，支持MP4格式                        【公众号】
     *                          缩略图（thumb）：64KB，支持 JPG 格式                    【公众号】
     * @param string $videoTitle
     * @param string $videoDescription
     *
     * @return array|bool|mixed|string
     * @throws Exception
     */
    public function upload(string $mediaType, string $filePath, string $type = "image", string $videoTitle = "", string $videoDescription = "")
    {
        $url = "";

        if ($mediaType == 10) {
            // 小程序临时图片
            $url = "cgi-bin/media/upload";
        } elseif ($mediaType == 20) {
            // 公众号临时素材
            $url = "cgi-bin/media/add_material";
        } elseif ($mediaType == 21) {
            // 公众号永久素材
            $url = "cgi-bin/material/add_material";
        }
        $this->url = $this->parseUrl($url, ["type" => $type]);
        $this->setApiUrl($url);

        $data = [];
        if ($type == "video") {
            $data["description"] = show_json([
                "title"        => $videoTitle,
                "introduction" => $videoDescription,
            ]);
        }
        $headers = [
            "Content-Disposition" => "form-data; name='media'; filename='" . basename($filePath) . "'",
        ];
        $result  = $this->http->setHeader($headers)->upload($this->url, $filePath, $data);
        return $this->getCurlResult($result, 'upload', $url, $data, [], $videoTitle, $videoDescription);
    }

    // ===========================================================================================
    // 之前实现的代码
    // ===========================================================================================


    /**
     * 上传非素材类文件
     *
     * @param string $url
     * @param string $filePath
     * @param        $params
     *
     * @return mixed
     * @throws Exception
     */
    public function customUpload(string $url = "", string $filePath = "", array $params = [])
    {
        $this->url = $this->parseUrl($url, $params);
        $this->setApiUrl($url);

        $headers = [
            "Content-Disposition" => "form-data; name='media'; filename='" . basename($filePath) . "'",
        ];
        $result  = $this->http->setHeader($headers)->upload($this->url, $filePath);
        if (isset($result["errcode"]) && $result["errcode"] > 0) {
            if (in_array($result["errcode"], $this->tryAgainCode)) {
                if ($this->tryAgainNum > $this->tryAgainMax) {
                    $this->enableToken(true);
                    $this->error("尝试多次请求都失败了!", $result["errcode"]);
                }
                $this->tryAgainNum++;
                $this->getAccessToken(true);
                return $this->customUpload($url, $filePath, $params);
            }
            $result["message"] = $this->getMessage($result["errcode"]);
        }
        $this->enableToken(true);
        $this->tryAgainNum = 0;
        return $result;
    }

    public function download(string $url = "", string $savePath = "", array $params = [])
    {
        $this->url = $this->parseUrl($url, $params);
        $this->setApiUrl($url);
        return $this->http->download($url, $savePath);
    }

    // 上传素材
    public function uploadFile($url, $filePath, $params = [], $videoTitle = "", $videoDescription = "")
    {
        $data = [];
        $type = "image";
        if (str_contains($filePath, '.mp4')) {
            $type = "video";
        }
        if ($type == "video") {
            $data["description"] = show_json([
                "title"        => $videoTitle,
                "introduction" => $videoDescription,
            ]);
        }
        $this->url = $this->parseUrl($url, $params);
        $this->setApiUrl($url);
        return $this->http->upload($this->url, $filePath, $data);
    }

    /**
     * 自定义操作回调
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function customCallback(callable $callback)
    {
        $callback($this);
        return $this;
    }
}
