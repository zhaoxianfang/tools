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
    protected $urlBase = "https://api.weixin.qq.com/API_URL?ACCESS_TOKEN";

    // 已经解析好的接口请求url地址
    protected $url = "";
    // 未解析的原始url, $urlBase 中的 API_URL
    protected $originalUrl = "";

    //curl 对象
    protected $http = "";

    // 缓存对象
    protected $cache = "";

    /**
     * @var object 对象实例数组
     */
    protected static $instance;

    // 请求接口时候需要的 access_token
    private $accessToken = "";

    // 接口url中是否使用 $accessToken 参数
    public $useToken = true;

    // Request 请求对象
    public $request;

    // 需要重新获取token请求的状态码
    private $tryAgainCode = ["40014", "40001", "41001", "42001"];

    // 当前重请求次数
    private $tryAgainNum = 0;

    // 允许最大重试次数
    private $tryAgainMax = 2;

    // 小程序配置
    protected $config = [
        "token"          => "",
        "appid"          => "",
        "appsecret"      => "",
        "encodingaeskey" => "",
        "token_callback" => "",
        // 配置商户支付参数（可选，在使用支付功能时需要）
        "mch_id"         => "",
        "mch_key"        => "",

        // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
        'ssl_key'        => '',
        'ssl_cer'        => '',
        // 缓存目录配置（可选，需拥有读写权限）
        'cache_path'     => '',
    ];

    public function __construct(array $config = [])
    {
        return $this->init($config);
    }

    /**
     * 静态创建对象
     *
     * @param array $config
     *
     * @return static
     */
    public static function instance(array $config = []): self
    {
        $key = md5(get_called_class() . (!empty($config) ? serialize($config) : ""));
        if (isset(self::$instance[$key]) && !empty(self::$instance[$key])) {
            return self::$instance[$key];
        }
        return self::$instance[$key] = new static($config);
    }

    /**
     * 初始化配置参数
     */
    private function init(array $config = []): self
    {
        $this->request = Request::instance();
        $this->http    = Curl::instance();
        $this->cache   = Cache::instance();

        if (empty($config)) {
            $config = $this->cache->get('lately_wechat_config', []);
        }
        $this->config = $config + $this->config;

        $this->config = new DataArray($this->config);

        if (empty($this->config["appid"])) {
            $this->error("Missing Config -- [appid]");
        }
        if (empty($this->config["appsecret"])) {
            $this->error("Missing Config -- [appsecret]");
        }
        if (empty($this->config["token"])) {
            $this->error("Missing Config -- [token]");
        }

        $this->url         = "";
        $this->accessToken = "";
        $this->tryAgainNum = 0;
        // 缓存最近一次的配置
        $this->cache->set('lately_wechat_config', $config);

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
        if (substr($url, 0, 4) != "http") {
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
    private function requestToken(): void
    {
        $this->useToken = false;
        $url            = $this->parseUrl("cgi-bin/token", [
            "grant_type" => "client_credential",
            "appid"      => $this->config["appid"],
            "secret"     => $this->config["appsecret"],
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
            "secret"        => $this->config["appsecret"],
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
        if (!empty($this->accessToken) || !empty($this->accessToken = $this->cache->get($this->config["appid"] . "_access_token"))) {
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
        $this->cache->set($this->config["appid"] . "_access_token", $accessToken, $expiresIn);
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
        return $this->cache->delete($this->config["appid"] . "_access_token");
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

        $baseUrl = substr($apiUrl, 0, 4) == "http" ? $apiUrl : $this->urlBase;

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
    public function post(string $url = "", array|string $data = [], $urlParams = [])
    {
        $this->url = $this->parseUrl($url, $urlParams);
        $result    = $this->http->setParams($data)->post($this->url);
        return $this->getCurlResult($result, 'post', $url, $data, $urlParams);
    }

    /**
     *  发送get 请求
     *
     * @param string       $url
     * @param array|string $data
     * @param string       $urlParams 拼接在url中的参数
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $url = "", array|string $data = [], $urlParams = [])
    {
        $this->url = $this->parseUrl($url, $urlParams);
        $result    = $this->http->setParams($data)->get($this->url);
        return $this->getCurlResult($result, 'get', $url, $data, $urlParams);
    }

    /**
     * 获取curl请求结果,如果请求失败,则尝试重新请求
     *
     * @param string       $funcStr 请求方法 get/post
     * @param              $curlResult
     * @param string       $url
     * @param array|string $data
     * @param              $urlParams
     *
     * @return mixed
     * @throws Exception
     */
    private function getCurlResult($curlResult, string $funcStr, string $url = "", array|string $data = [], $urlParams = [], ...$args)
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
            $data["description"] = json_encode(
                [
                    "title"        => $videoTitle,
                    "introduction" => $videoDescription,
                ],
                JSON_UNESCAPED_UNICODE
            );
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

    public function download($url = "", $savePath = "", array $params = [])
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
            $data["description"] = json_encode(
                [
                    "title"        => $videoTitle,
                    "introduction" => $videoDescription,
                ],
                JSON_UNESCAPED_UNICODE
            );
        }
        $this->url = $this->parseUrl($url, $params);
        $this->setApiUrl($url);
        return $this->http->upload($this->url, $filePath, $data);
    }
}
