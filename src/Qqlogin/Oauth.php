<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

namespace zxf\Qqlogin;

use Exception;

// require_once(CLASS_PATH."Recorder.class.php");
// require_once(CLASS_PATH."URL.class.php");
// require_once(CLASS_PATH."ErrorCase.class.php");

class Oauth
{

    const VERSION              = "2.0";
    const GET_AUTH_CODE_URL    = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL       = "https://graph.qq.com/oauth2.0/me";

    public $urlUtils;

    public $state;
    public $appid    = "";
    public $appkey   = "";
    public $callback = "";
    public $scope    = "get_user_info";

    // 存储token等 的数据仓库
    // public $wareroom    = array();

    //错误提示
    public $errorMsg = array(
        "20001" => "读取配置失败", //"配置文件损坏或无法读取，请重新执行intall",
        "30001" => "数据验证失败，可能存在CSRF攻击", //"The state does not match. You may be a victim of CSRF.",
        "50001" => "可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们",
    );

    /**
     * [__construct description]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-01
     * @param array $config [qq配置参数] （appid、appkey、callbackUrl）
     */
    public function __construct($config = array())
    {

        if (!$config['appid'] || !$config['appkey'] || !$config['callbackUrl']) {
            // throw new Exception("缺少必要的参数： appid、appkey、callbackUrl");
            throw new Exception($this->errorMsg['20001']);
        }

        if (!isset($_SESSION) || session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->appid  = $config['appid'];
        $this->appkey = $config['appkey'];
        // $this->callback = urlencode($config['callbackUrl']);
        $this->callback = $config['callbackUrl'];
        $this->urlUtils = new URL();

    }

    /**
     * QQ登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param string $stateSys [可选] [可以为字符串或者数组]用于callback回调的数据
     * @return   [type]                 [description]
     */
    public function qq_login($stateSys = '')
    {

        $state = $stateSys ? base64_encode(json_encode($stateSys)) : base64_encode(json_encode('null'));
        $state = $this->strCode($state, 'en');

        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id"     => $this->appid,
            "redirect_uri"  => $this->callback,
            "state"         => $state,
            "scope"         => $this->scope,
        );

        $login_url = $this->urlUtils->combineURL(self::GET_AUTH_CODE_URL, $keysArr);
        return $login_url;
        // header("Location:$login_url");
    }

    public function qq_callback()
    {
        //--------验证state防止CSRF攻击
        if (!empty($_REQUEST['code']) && empty($_REQUEST['state'])) {
            // throw new Exception($this->errorMsg['30001']);
        }
        if (!empty($_REQUEST['state'])) {
            $state = urldecode($_REQUEST['state']);
            $state = str_replace(' ', '+', $state);
            // 进行解密 验证是否为本站发出的state
            $decodeStr = $this->strCode($state, 'de');

            try {
                $userParam = json_decode(base64_decode($decodeStr), true);
            } catch (Exception $e) {
                throw new Exception($this->errorMsg['30001']);
            }
        } else {
            $userParam = [];
        }

        //-------请求参数列表
        $keysArr = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->appid,
            "redirect_uri"  => urlencode($this->callback),
            "client_secret" => $this->appkey,
            "code"          => $_GET['code'],
        );

        //------构造请求access_token的url
        $token_url = $this->urlUtils->combineURL(self::GET_ACCESS_TOKEN_URL, $keysArr);
        $response  = $this->urlUtils->get_contents($token_url);

        if (strpos($response, "callback") !== false) {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
            $msg      = json_decode($response);

            if (isset($msg->error)) {
                throw new Exception($msg->error . "：" . $msg->error_description);
            }
        }

        $params = array();
        parse_str($response, $params);
        $_SESSION['access_token'] = $params["access_token"];

        return $userParam;
    }

    public function get_openid()
    {
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $_SESSION['access_token'],
        );

        $graph_url = $this->urlUtils->combineURL(self::GET_OPENID_URL, $keysArr);
        $response  = $this->urlUtils->get_contents($graph_url);

        //--------检测错误是否发生
        if (strpos($response, "callback") !== false) {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $user = json_decode($response);
        if (isset($user->error)) {
            throw new Exception($user->error . "：" . $user->error_description);
        }

        //------记录openid
        $_SESSION['openid'] = $user->openid;
        return $user->openid;

    }

    /**
     * 字符串加解密
     * @Author   ZhaoXianFang
     * @DateTime 2019-04-01
     * @param    [type]       $string [字符串]
     * @param string $action [en:加密；de:解密]
     * @return   [type]               []
     */
    private function strCode($string, $action = 'en')
    {
        $action != 'en' && $string = base64_decode($string);
        $code   = '';
        $key    = 'str_en_de_code';
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k    = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'de' ? base64_encode($code) : $code);
    }
}
