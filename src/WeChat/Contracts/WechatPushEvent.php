<?php

namespace zxf\WeChat\Contracts;

use Exception;
use zxf\Tools\DataArray;
use zxf\WeChat\Prpcrypt\Prpcrypt;

/**
 * 微信通知处理基本类
 */
class WechatPushEvent extends WeChatBase
{
    public bool $useToken = false;

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
     * 公众号推送内容对象
     *
     * @var DataArray
     */
    protected $receive;

    /**
     * 准备回复的消息内容
     *
     * @var array
     */
    protected $message;

    /**
     * 公众号的推送请求参数
     *
     * @var DataArray
     */
    protected $input;

    public function __construct(string $key = 'default')
    {
        parent::__construct($key);
        // 参数初始化
        // $this->input = new DataArray($_REQUEST);
        $this->input = new DataArray(get_raw_input(false));

        $this->appid = $this->config->get('appid');
        // 推送消息处理
        $method = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($method == "POST") {
            $this->postxml     = $this->getRawInput();
            $this->encryptType = $this->input->get('encrypt_type');
            if ($this->isEncrypt()) {
                if (empty($options['aes_key'])) {
                    return $this->error('Missing Config -- [aes_key]!', 400);
                }
                $prpcrypt = new Prpcrypt($this->config->get('aes_key'));
                // $result   = $this->xml2arr($this->postxml);
                $result   = get_raw_input(false);
                $array    = $prpcrypt->decrypt($result['Encrypt']);
                if (intval($array[0]) > 0) {
                    return $this->error($array[1], $array[0]);
                }
                list($this->postxml, $this->appid) = [$array[1], $array[2]];
            }
            // $this->receive = new DataArray($this->xml2arr($this->postxml));
            $this->receive = $this->input;
        } elseif ($method == "GET" && $this->checkSignature()) {
            @ob_clean();
            exit($this->input->get('echostr'));
        } else {
            $this->error('Access Denied.', 403);
        }
    }

    /**
     * 获取输入对象
     *
     * @return false|mixed|string
     */
    public function getRawInput()
    {
        return get_raw_input();
    }

    /**
     * 解析XML内容到数组
     *
     * @param string $xml
     *
     * @return array
     */
    public function xml2arr(string $xml)
    {
        return \zxf\Xml\XML2Array::parse($xml);
    }

    /**
     * 数组转XML内容
     *
     * @param array $data
     *
     * @return string
     */
    public function arr2xml(array $data)
    {
        return \zxf\Xml\Array2XML::createWechatXML($data);
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
    public function reply(array $data = [], bool $return = false, bool $isEncrypt = false)
    {
        $xml = $this->arr2xml(empty($data) ? $this->message : $data);
        if ($this->isEncrypt() || $isEncrypt) {
            $prpcrypt = new Prpcrypt($this->config->get('aes_key'));
            // 如果是第三方平台，加密得使用 component_appid
            $component_appid = $this->config->get('component_appid');
            $appid           = empty($component_appid) ? $this->appid : $component_appid;
            $array           = $prpcrypt->encrypt($xml, $appid);
            if ($array[0] > 0) {
                return $this->error('Encrypt Error.');
            }
            list($timestamp, $encrypt) = [time(), $array[1]];
            $nonce  = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $tmpArr = [$this->config->get('token'), $timestamp, $nonce, $encrypt];
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
     * @return bool
     */
    private function checkSignature()
    {
        $nonce         = $this->input->get('nonce');
        $timestamp     = $this->input->get('timestamp');
        $msg_signature = $this->input->get('msg_signature');
        $signature     = empty($msg_signature) ? $this->input->get('signature') : $msg_signature;
        $tmpArr        = [$this->config->get('token'), $timestamp, $nonce, ''];
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