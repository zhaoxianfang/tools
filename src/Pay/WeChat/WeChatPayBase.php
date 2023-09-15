<?php

namespace zxf\Pay\WeChat;

use Exception;
use zxf\Facade\Curl;
use zxf\Facade\Request;
use zxf\Pay\Traits\CallbackTrait;
use zxf\Pay\Traits\CombineTrait;
use zxf\Pay\Traits\ConfigTrait;
use zxf\Pay\Traits\HttpTrait;
use zxf\Pay\Traits\SignTrait;
use zxf\Pay\Traits\withHeaderSerialTrait;
use zxf\Pay\Traits\MediaTrait;
use zxf\tools\Cache;
use zxf\tools\DataArray;

/**
 * docs:https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_5_1.shtml
 */
abstract class WeChatPayBase
{
    use SignTrait, ConfigTrait, CombineTrait, CallbackTrait, HttpTrait, withHeaderSerialTrait, MediaTrait;

    // 微信支付请求地址
    protected string $urlBase = "https://api.mch.weixin.qq.com/API_URL";

    // 微信支付备用请求地址 https://pay.weixin.qq.com/wiki/doc/apiv3/Practices/chapter1_1_4.shtml
    protected string $backupUrlBase = "https://api2.mch.weixin.qq.com/API_URL";

    // Request 请求对象
    public $request;

    //curl 对象
    protected $http;

    // 缓存对象
    protected ?Cache $cache;

    // 小程序配置
    protected array|DataArray $config = [
        // 普通商户
        "appid"     => "", // 应用ID
        "mchid"     => "", // 直连商户号

        // 服务商
        "sp_appid"  => "", // 服务商应用ID
        "sp_mchid"  => "", // 服务商户号
        "sub_mchid" => "", // 子商户号(可选，可在请求参数中传入)

        "v3_secret_key"    => "", // 在商户平台上设置的APIv3密钥
        "wechatpay_serial" => "", // 通过微信wechatpay安装包下载的证书文件内容

        'apiclient_key'  => '', // 商户私钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)
        'apiclient_cert' => '', // 商户公钥('.cer', '.crt', '.pem' 后缀的证书文件路径 或者 内容字符串)
        "notify_url"     => "", // 默认的异步通知地址，通知URL必须为直接可访问的URL，不允许携带查询串，要求必须为https地址

        'cache_path' => '',// 缓存目录配置（可选，需拥有读写权限）
    ];

    /**
     * 普通商户模式.
     */
    public const MODE_MERCHANT = 'merchant';

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 'service';

    protected string $mode = self::MODE_MERCHANT;

    // 当前请求的url (API_URL部份)
    protected string $url = '';

    // 当前请求的body
    protected $body;

    // 追加到body或url的参数字段，例如 appid|sub_mchid 等
    protected array $withRequestFields = [];

    // 当前请求的headers
    protected array $headers = [];

    public function __construct(string $connectionName = 'default')
    {
        $this->request = Request::instance();
        $this->http    = Curl::instance();
        $this->initConfig($connectionName);
    }

    /**
     * 设置服务模式
     */
    public function setMode($mode = self::MODE_MERCHANT)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * 判断是否为服务商模式
     */
    public function isService(): bool
    {
        return $this->mode === self::MODE_SERVICE;
    }

    /**
     * 设置请求的 API_URL 部份
     *
     * @param string $url
     *
     * @return $this
     */
    public function url(string $url = '')
    {
        $this->url = $url;
        return $this;
    }

    public function withRequestFields(array $params = [])
    {
        $this->withRequestFields = $params;
        return $this;
    }

    public function body(array $body = [], bool $toString = false)
    {
        $this->body = $toString ? json_encode($body) : $body;
        return $this;
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
     * 请求内容字段中追加$config 里面的参数 $this->withRequestFields
     *
     * @param array $data
     *
     * @return array
     */
    public function appendBody(array &$data = [])
    {
        if (empty($this->withRequestFields)) {
            return $data;
        }
        foreach ($this->withRequestFields as $key) {
            if (!isset($data[$key]) && isset($this->config[$key])) {
                $data[$key] = $this->config[$key];
            }
        }
        // empty($data['notify_url']) || $data['notify_url'] = $this->config['notify_url']; // 通知地址,异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。 公网域名必须为https，如果是走专线接入，使用专线NAT IP或者私有回调域名可使用http
        return $data;
    }


    protected function clear()
    {
        $this->url     = '';
        $this->body    = [];
        $this->headers = [];
        $this->withRequestFields([]);
    }
}