<?php

namespace zxf\WeChat;

use zxf\req\Curl;
use zxf\tools\Cache;

class WeChatBase
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

    public $type = 'mini_program'; // mini_program（微信小程序） 或者 official_account（微信公众号）

    // 小程序配置
    protected $config = [
        'app_id' => '',
        'secret' => '',
    ];

    // 直播错误码参照
    public static $errCode = [
        "-1"      => "系统错误",
        "0"       => "操作成功",
        "1"       => "未创建直播间",
        "500"     => "未知的错误", // 此错误码为自定义的错误码
        "1003"    => "商品 id 不存在",
        "40001"   => "AppSecret错误或者 AppSecret 不属于这个公众号",
        "40002"   => "请确保grant_type字段值为client_credential",
        "40004"   => "无效的媒体类型",
        "40007"   => "无效的media_id",
        "40164"   => "调用接口的 IP 地址不在白名单中",
        "41001"   => "无效的accesstoken",
        "40013"   => "不合法的 AppID",
        "40097"   => "参数错误",
        "48001"   => "无权限调用该api",
        "42001"   => "accesstoken过期",
        "47001"   => "入参格式不符合规范",
        "89501"   => "此 IP 正在等待管理员确认,请联系管理员",
        "89503"   => "此 IP 调用需要管理员确认,请联系管理员",
        "89506"   => "24小时内该 IP 被管理员拒绝调用两次，24小时内不可再使用该 IP 调用",
        "89507"   => "1小时内该 IP 被管理员拒绝调用一次，1小时内不可再使用该 IP 调用",
        "200001"  => "入参错误",
        "200002"  => "入参错误",
        "300001"  => "禁止创建/更新商品 或 禁止编辑&更新房间",
        "300002"  => "名称长度不符合规则",
        "300006"  => "图片上传失败（如=>mediaID过期）",
        "300022"  => "此房间号不存在",
        "300023"  => "房间状态 拦截（当前房间状态不允许此操作）",
        "300024"  => "商品不存在",
        "300025"  => "商品审核未通过",
        "300026"  => "房间商品数量已经满额",
        "300027"  => "导入商品失败",
        "300028"  => "房间名称违规",
        "300029"  => "主播昵称违规",
        "300030"  => "主播微信号不合法",
        "300031"  => "直播间封面图不合规",
        "300032"  => "直播间分享图违规",
        "300033"  => "添加商品超过直播间上限",
        "300034"  => "主播微信昵称长度不符合要求",
        "300035"  => "主播微信号不存在",
        "300036"  => "主播微信号未实名认证",
        "300037"  => "购物直播频道封面图不合规",
        "300038"  => "未在小程序管理后台配置客服",
        "300039"  => "主播副号微信号不合法",
        "300040"  => "名称含有非限定字符（含有特殊字符）",
        "300041"  => "创建者微信号不合法",
        "300042"  => "推流中禁止编辑房间",
        "300043"  => "每天只允许一场直播开启关注",
        "300044"  => "商品没有讲解视频",
        "300045"  => "讲解视频未生成",
        "300046"  => "讲解视频生成失败",
        "300047"  => "已有商品正在推送，请稍后再试",
        "300048"  => "拉取商品列表失败",
        "300049"  => "商品推送过程中不允许上下架",
        "300050"  => "排序商品列表为空",
        "300051"  => "解析 JSON 出错",
        "300052"  => "已下架的商品无法推送",
        "300053"  => "直播间未添加此商品",
        "500001"  => "副号不合规",
        "500002"  => "副号未实名",
        "500003"  => "已经设置过副号了，不能重复设置",
        "500004"  => "不能设置重复的副号",
        "500005"  => "副号不能和主号重复",
        "600001"  => "用户已被添加为小助手",
        "600002"  => "找不到用户",
        "9410000" => "直播间列表为空",
        "9410001" => "获取房间失败",
        "9410002" => "获取商品失败",
        "9410003" => "获取回放失败",
    ];


    public function __construct(array $config = [])
    {
        $this->initConfig($config);
    }

    public static function instance(array $config = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($config);
        }

        return self::$instance;
    }


    /**
     * 初始化配置参数
     *
     * @return $this
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function initConfig(array $config = [])
    {
        $config = !empty($config) ? array_merge($this->config, $config) : [];
        if (empty($config['app_id']) || empty($config['secret'])) {
            throw new \Exception('确少微信小程序配置参数:app_id或secret');
        }
        $this->config = $config;

        $this->http  = Curl::instance();
        $this->cache = Cache::instance([
            'cache_dir' => !empty($config['cache_dir']) ? $config['cache_dir'] : "/cache",
            'type'      => !empty($config['cache_type']) ? $config['cache_type'] : "random",
            'mode'      => !empty($config['cache_mode']) ? $config['cache_mode'] : 1,
        ]);

        $this->getToken();
        return $this;
    }

    /**
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
     * @param string $apiUrl
     *
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function generateRequestUrl(string $apiUrl = ''): string
    {
        if (empty($apiUrl)) {
            throw new \Exception('接口请求地址不能为空');
        }
        if (empty($this->accessToken)) {
            $this->getToken(true);
        }
        $this->url = str_replace(['API_URL', 'ACCESS_TOKEN'], [$apiUrl, $this->accessToken], $this->urlBase);
        return $this->url;
    }

    /**
     * 获取 access_token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        if (!$this->accessToken = $this->cache->get('wechat_accessToken')) {
            $this->initConfig();
        }
        return $this->accessToken;
    }

    /**
     * @return string[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 去微信请求 access_token 参数
     *
     * @return $this|\Illuminate\Http\Client\Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getToken($refreshToken = false)
    {
        return $this->getTokenByCustom($refreshToken);
    }

    /**
     * 原生获取token
     *
     * @return $this|\Illuminate\Http\Client\Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function getTokenByCustom($refreshToken = false)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['app_id']}&secret={$this->config['secret']}";
        $res = $this->http->get($url);

        $this->accessToken = $res['access_token'];
        // 缓存token
        $this->cache->set('wechat_accessToken', $this->accessToken, 7000);
        return $this;
    }

    /**
     * 获取状态码
     *
     * @param string $code
     *
     * @return string
     */
    public function getCode(string $code = ''): string
    {
        return !empty(self::$errCode[$code]) ? self::$errCode[$code] : self::$errCode['500'];
    }

    /**
     * 发送 post 请求
     *
     * @param string $url
     * @param array  $data
     *
     * @return array|\Illuminate\Http\Client\Response|mixed
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function post(string $url = '', array $data = [])
    {
        $this->generateRequestUrl($url);

        $result = $this->http->setParams($data)->post($this->url);

        if (isset($result['errcode']) && $result['errcode'] == '40001') {
            $this->getToken(true);
            return $this->post($url, $data);
        }
        $result['message'] = $this->getCode($result['errcode']);
        return $result;

    }

    /**
     * 发送get 请求
     *
     * @param string $url
     * @param array  $data
     *
     * @return array|\Illuminate\Http\Client\Response|mixed
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function get(string $url = '', array $data = [])
    {
        $this->generateRequestUrl($url);

        $result = $this->http->setParams($data)->get($this->url);

        if (isset($result['errcode']) && $result['errcode'] == '40001') {
            $this->getToken(true);
            return $this->get($url, $data);
        }
        $result['message'] = $this->getCode($result['errcode']);
        return $result;
    }

}
