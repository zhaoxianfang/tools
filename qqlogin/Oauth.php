<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */
namespace zxf\tool\qqlogin;
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

    //错误提示
    public $errorMsg = array(
        "20001" => "配置文件损坏或无法读取，请重新执行intall",
        "30001" => "The state does not match. You may be a victim of CSRF.",
        "50001" => "可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们"
    );

    /**
     * [__construct description]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-01
     * @param    array        $config [qq配置参数] （appid、appkey、callbackUrl）
     */
    public function __construct($config = array())
    {
        if(!$config['appid'] || !$config['appkey'] || !$config['callbackUrl']){
            throw new Exception("缺少必要的参数： appid、appkey、callbackUrl");
        }
        $this->appid = $config['appid'];
        $this->appkey = $config['appkey'];
        // $this->callback = urlencode($config['callbackUrl']);
        $this->callback = $config['callbackUrl'];
        $this->urlUtils = new URL();
        
    }

    /**
     * QQ登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    string       $jump [跳转地址]
     * @return   [type]             [description]
     */
    public function qq_login()
    {

        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), true));
        session('state', $state);

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
        // if(!$state || $_GET['state'] != $state){
        if (input('state') != session('state')) {
            // exit('30001');
            throw new Exception($this->errorMsg['30001']);
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
                throw new Exception($msg->error."：".$msg->error_description);
                
                // $this->error->showError($msg->error, $msg->error_description);
            }
        }

        $params = array();
        parse_str($response, $params);

        // $this->recorder->write("access_token", $params["access_token"]);
        // return $params["access_token"];
        session('access_token', $params["access_token"]);

    }

    public function get_openid()
    {

        //-------请求参数列表
        $keysArr = array(
            "access_token" => session('access_token'),
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
            // $this->error->showError($user->error, $user->error_description);
            throw new Exception($msg->error."：".$msg->error_description);
        }

        //------记录openid
        // $this->recorder->write("openid", $user->openid);
        session("openid", $user->openid);
        return $user->openid;

    }
}
