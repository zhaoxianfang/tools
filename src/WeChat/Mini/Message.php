<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序动态消息
 * Class Message
 * @package WeMini
 */
class Message extends WeChatBase
{
    /**
     * 动态消息，创建被分享动态消息的 activity_id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createActivityId($data)
    {
        $url = 'cgi-bin/message/wxopen/activityid/create';
        return $this->post($url, $data);
    }

    /**
     * 动态消息，修改被分享的动态消息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setUpdatableMsg($data)
    {
        $url = 'cgi-bin/message/wxopen/updatablemsg/send';
        return $this->post($url, $data);
    }

    /**
     * 下发小程序和公众号统一的服务消息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function uniformSend($data)
    {
        $url = 'cgi-bin/message/wxopen/template/uniform_send';
        return $this->post($url, $data);
    }
}