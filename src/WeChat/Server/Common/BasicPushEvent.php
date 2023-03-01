<?php

namespace zxf\WeChat\Server\Common;

use Exception;
use zxf\Facade\Xml;
use zxf\tools\DataArray;
use zxf\WeChat\Server\Prpcrypt\Prpcrypt;
use zxf\WeChat\WeChatBase;

/**
 * 微信通知处理基本类
 * Class BasicPushEvent
 *
 * @package WeChat\Contracts
 */
class BasicPushEvent extends WeChatBase
{
    /**
     * 公众号APPID
     *
     * @var string
     */
    protected $appid;

    /**
     * 公众号推送XML内容
     *
     * @var string
     */
    protected $postxml;

    /**
     * 公众号推送加密类型
     *
     * @var string
     */
    protected $encryptType;

    /**
     * 公众号的推送请求参数
     */
    protected $input;

    /**
     * 公众号推送内容对象
     */
    protected $receive;

    /**
     * 准备回复的消息内容
     *
     * @var array
     */
    protected $message;

    /**
     * BasicPushEvent constructor.
     *
     * @param array $options
     *
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        // 参数初始化
        $this->appid = $this->config['appid'];
        // 推送消息处理

        if ($this->request->method() == "POST") {
            $this->postxml     = file_get_contents("php://input");
            $this->encryptType = $this->request->get('encrypt_type');
            if ($this->isEncrypt()) {
                if (empty($options['encodingaeskey'])) {
                    throw new Exception("Missing Config -- [encodingaeskey]");
                }
                $prpcrypt = new Prpcrypt($this->config['encodingaeskey']);
                $result   = $this->request->post();
                $array    = $prpcrypt->decrypt($result['Encrypt']);
                if (intval($array[0]) > 0) {
                    throw new Exception($array[1], $array[0]);
                }
                list($this->postxml, $this->appid) = [$array[1], $array[2]];
            }
            $this->receive = new DataArray($this->request->all());
        } elseif ($this->request->method() == "GET" && $this->checkSignature()) {
            @ob_clean();
            exit($this->request->get('echostr'));
        } else {
            throw new Exception('Invalid interface request.', '0');
        }
    }

    /**
     * 消息是否需要加密
     *
     * @return boolean
     */
    public function isEncrypt()
    {
        return $this->encryptType === 'aes';
    }

    /**
     * 回复消息
     *
     * @param array   $data      消息内容
     * @param boolean $return    是否返回XML内容
     * @param boolean $isEncrypt 是否加密内容
     *
     * @return string
     * @throws Exception
     */
    public function reply(array $data = [], $return = false, $isEncrypt = false)
    {
        $xml = Xml::array2xml(empty($data) ? $this->message : $data);
        if ($this->isEncrypt() || $isEncrypt) {
            $prpcrypt = new Prpcrypt($this->config['encodingaeskey']);
            // 如果是第三方平台，加密得使用 component_appid
            $component_appid = $this->config['component_appid'];
            $appid           = empty($component_appid) ? $this->appid : $component_appid;
            $array           = $prpcrypt->encrypt($xml, $appid);
            if ($array[0] > 0) {
                throw new Exception('Encrypt Error.', '0');
            }
            list($timestamp, $encrypt) = [time(), $array[1]];
            $nonce  = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $tmpArr = [$this->config['token'], $timestamp, $nonce, $encrypt];
            sort($tmpArr, SORT_STRING);
            $signature = sha1(implode($tmpArr));
            $format    = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
            $xml       = sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }
        if ($return) {
            return $xml;
        }
        @ob_clean();
        echo $xml;
    }

    /**
     * 验证来自微信服务器
     *
     * @param string $str
     *
     * @return bool
     */
    private function checkSignature($str = '')
    {
        $nonce         = $this->request->get('nonce');
        $timestamp     = $this->request->get('timestamp');
        $msg_signature = $this->request->get('msg_signature');
        $signature     = empty($msg_signature) ? $this->request->get('signature') : $msg_signature;
        $tmpArr        = [$this->config['token'], $timestamp, $nonce, $str];
        sort($tmpArr, SORT_STRING);
        return sha1(implode($tmpArr)) === $signature;
    }

    /**
     * 获取公众号推送对象
     *
     * @param null|string $field 指定获取字段
     *
     * @return array
     */
    public function getReceive($field = null)
    {
        return $this->receive->get($field);
    }

    /**
     * 获取当前微信OPENID
     *
     * @return string
     */
    public function getOpenid()
    {
        return $this->receive->get('FromUserName');
    }

    /**
     * 获取当前推送消息类型
     *
     * @return string
     */
    public function getMsgType()
    {
        return $this->receive->get('MsgType');
    }

    /**
     * 获取当前推送消息ID
     *
     * @return string
     */
    public function getMsgId()
    {
        return $this->receive->get('MsgId');
    }

    /**
     * 获取当前推送时间
     *
     * @return integer
     */
    public function getMsgTime()
    {
        return $this->receive->get('CreateTime');
    }

    /**
     * 获取当前推送公众号
     *
     * @return string
     */
    public function getToOpenid()
    {
        return $this->receive->get('ToUserName');
    }
}