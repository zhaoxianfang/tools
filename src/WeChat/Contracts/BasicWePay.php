<?php


namespace zxf\WeChat\Contracts;

use zxf\Facade\Random;
use zxf\Facade\Xml;
use zxf\tools\DataArray;
use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信支付基础类
 * Class BasicPay
 *
 * @package zxf\WeChat\Contracts
 */
class BasicWePay extends WeChatBase
{
    // 微信请求地址
    private $urlBase = "https://api.mch.weixin.qq.com/API_URL?ACCESS_TOKEN";

    // 接口url中是否使用 $accessToken 参数
    public $useToken = false;

    /**
     * 当前请求数据
     */
    protected $params;


    /**
     * WeChat constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (empty($options["mch_id"])) {
            throw new Exception("Missing Config -- [mch_id]");
        }
        if (empty($options["mch_key"])) {
            throw new Exception("Missing Config -- [mch_key]");
        }
        // 商户基础参数
        $this->params = new DataArray([
            "appid"     => $this->config["appid"],
            "mch_id"    => $this->config["mch_id"],
            "nonce_str" => Random::alnum(32),
        ]);
        // 商户参数支持
        if ($this->config["sub_appid"]) {
            $this->params->set("sub_appid", $this->config["sub_appid"]);
        }
        if ($this->config["sub_mch_id"]) {
            $this->params->set("sub_mch_id", $this->config["sub_mch_id"]);
        }
    }

    /**
     * 获取微信支付通知
     *
     * @return array
     * @throws Exception
     */
    public function getNotify()
    {
        $data = $this->request->all();
        if (isset($data["sign"]) && $this->getPaySign($data) === $data["sign"]) {
            return $data;
        }
        throw new Exception("Invalid Notify.", "0");
    }

    /**
     * 获取微信支付通知回复内容
     *
     * @return string
     */
    public function getNotifySuccessReply()
    {
        return Xml::arr2xml(["return_code" => "SUCCESS", "return_msg" => "OK"]);
    }

    /**
     * 生成支付签名
     *
     * @param array  $data     参与签名的数据
     * @param string $signType 参与签名的类型
     * @param string $buff     参与签名字符串前缀
     *
     * @return string
     */
    public function getPaySign(array $data, $signType = "MD5", $buff = "")
    {
        ksort($data);
        if (isset($data["sign"])) {
            unset($data["sign"]);
        }
        foreach ($data as $k => $v) {
            if ("" === $v || null === $v) {
                continue;
            }
            $buff .= "{$k}={$v}&";
        }
        $buff .= ("key=" . $this->config["mch_key"]);
        if (strtoupper($signType) === "MD5") {
            return strtoupper(md5($buff));
        }
        return strtoupper(hash_hmac("SHA256", $buff, $this->config["mch_key"]));
    }

    /**
     * 转换短链接
     *
     * @param string $longUrl 需要转换的URL，签名用原串，传输需URLencode
     *
     * @return array
     * @throws Exception
     */
    public function shortUrl($longUrl)
    {
        return $this->enableToken(false)->post("tools/shorturl", ["long_url" => $longUrl]);
    }

    /**
     * 数组直接转xml数据输出
     *
     * @param array $data
     * @param bool  $isReturn
     *
     * @return string
     */
    public function toXml(array $data, $isReturn = false)
    {
        $xml = Xml::arr2xml($data);
        if ($isReturn) {
            return $xml;
        }
        echo $xml;
    }

    /**
     * 以 Post 请求接口
     *
     * @param string $url          请求
     * @param array  $data         接口参数
     * @param bool   $isCert       是否需要使用双向证书
     * @param string $signType     数据签名类型 MD5|SHA256
     * @param bool   $needSignType 是否需要传签名类型参数
     * @param bool   $needNonceStr
     *
     * @return array
     * @throws Exception
     */
    protected function callPostApi($url, array $data, $isCert = false, $signType = "HMAC-SHA256", $needSignType = true, $needNonceStr = true)
    {
        $option = [];
        if ($isCert) {
            $option["ssl_p12"] = $this->config["ssl_p12"];
            $option["ssl_cer"] = $this->config["ssl_cer"];
            $option["ssl_key"] = $this->config["ssl_key"];
            if (is_string($option["ssl_p12"]) && file_exists($option["ssl_p12"])) {
                $content = file_get_contents($option["ssl_p12"]);
                if (openssl_pkcs12_read($content, $certs, $this->config["mch_id"])) {
                    $option["ssl_key"] = $this->cache->pushFile(md5($certs["pkey"]) . ".pem", $certs["pkey"]);
                    $option["ssl_cer"] = $this->cache->pushFile(md5($certs["cert"]) . ".pem", $certs["cert"]);
                } else {
                    throw new Exception("P12 certificate does not match MCH_ID --- ssl_p12");
                }
            }
            if (empty($option["ssl_cer"]) || !file_exists($option["ssl_cer"])) {
                throw new Exception("Missing Config -- ssl_cer", "0");
            }
            if (empty($option["ssl_key"]) || !file_exists($option["ssl_key"])) {
                throw new Exception("Missing Config -- ssl_key", "0");
            }
        }
        $params = $this->params->merge($data);
        if (!$needNonceStr) {
            unset($params["nonce_str"]);
        }
        if ($needSignType) {
            $params["sign_type"] = strtoupper($signType);
        }
        $params["sign"] = $this->getPaySign($params, $signType);
        $result         = Xml::arr2xml($this->payPost($url, Xml::arr2xml($params), $option));
        if ($result["return_code"] !== "SUCCESS") {
            throw new Exception($result["return_msg"], "0");
        }
        return $result;
    }

    /**
     * CURL模拟网络请求
     *
     * @param string $method  请求方法
     * @param string $url     请求方法
     * @param array  $options 请求参数[headers,data,ssl_cer,ssl_key]
     *
     * @return boolean|string
     * @throws Exception
     */
    public function payPost($method, $url, $options = [])
    {
        // GET参数设置
        if (!empty($options["query"])) {
            $url .= (stripos($url, "?") !== false ? "&" : "?") . http_build_query($options["query"]);
        }

        return $this->http->inject(function ($http) use ($method, $options) {
            // 证书文件设置
            if (!empty($options["ssl_cer"])) {
                if (file_exists($options["ssl_cer"])) {
                    curl_setopt($http->ch, CURLOPT_SSLCERTTYPE, "PEM");
                    curl_setopt($http->ch, CURLOPT_SSLCERT, $options["ssl_cer"]);
                } else {
                    throw new Exception("Certificate files that do not exist. --- [ssl_cer]");
                }
            }
            // 证书文件设置
            if (!empty($options["ssl_key"])) {
                if (file_exists($options["ssl_key"])) {
                    curl_setopt($http->ch, CURLOPT_SSLKEYTYPE, "PEM");
                    curl_setopt($http->ch, CURLOPT_SSLKEY, $options["ssl_key"]);
                } else {
                    throw new Exception("Certificate files that do not exist. --- [ssl_key]");
                }
            }
        })
            ->showResponseHeader(false)
            ->setParams($options["data"])
            ->setTimeout(60)
            ->setHeader($options["headers"])
            ->post($url);

    }
}
