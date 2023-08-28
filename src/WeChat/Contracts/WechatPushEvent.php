<?php

namespace zxf\WeChat\Contracts;

use Exception;
use zxf\tools\DataArray;
use zxf\WeChat\Prpcrypt\Prpcrypt;

/**
 * 微信通知处理基本类
 */
class WechatPushEvent extends WeChatBase
{
    public $useToken = false;

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


    public function __construct(array $options)
    {
        parent::__construct($options);
        // 参数初始化
        $this->input = new DataArray($_REQUEST);
        $this->appid = $this->config->get('appid');
        // 推送消息处理
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->postxml     = $this->getRawInput();
            $this->encryptType = $this->input->get('encrypt_type');
            if ($this->isEncrypt()) {
                if (empty($options['encodingaeskey'])) {
                    return $this->error('Missing Config -- [encodingaeskey]!', 400);
                }
                $prpcrypt = new Prpcrypt($this->config->get('encodingaeskey'));
                $result   = $this->xml2arr($this->postxml);
                $array    = $prpcrypt->decrypt($result['Encrypt']);
                if (intval($array[0]) > 0) {
                    return $this->error($array[1], $array[0]);
                }
                list($this->postxml, $this->appid) = [$array[1], $array[2]];
            }
            $this->receive = new DataArray($this->xml2arr($this->postxml));
        } elseif ($_SERVER['REQUEST_METHOD'] == "GET" && $this->checkSignature()) {
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
        if (empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            return file_get_contents('php://input');
        } else {
            return $GLOBALS['HTTP_RAW_POST_DATA'];
        }
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
        if (PHP_VERSION_ID < 80000) {
            $backup = libxml_disable_entity_loader(true);
            $data   = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            libxml_disable_entity_loader($backup);
        } else {
            $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return json_decode(json_encode($data), true);
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
        return "<xml>" . $this->arr2xmlAchieve($data) . "</xml>";
    }


    /**
     * XML内容生成
     *
     * @param array  $data 数据
     * @param string $content
     *
     * @return string
     */
    private function arr2xmlAchieve(array $data, string $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = 'item';
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= $this->arr2xmlAchieve($val);
            } elseif (is_string($val)) {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            } else {
                $content .= $val;
            }
            $content .= "</{$key}>";
        }
        return $content;
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
            $prpcrypt = new Prpcrypt($this->config->get('encodingaeskey'));
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