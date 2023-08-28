<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 小程序动态消息
 */
class Message extends WeChatBase
{
    public $useToken = true;

    /**
     * 动态消息，创建被分享动态消息的 activity_id
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/updatable-message/createActivityId.html
     *
     * @param string|null $unionid 为私密消息创建activity_id时，指定分享者为unionid用户。其余用户不能用此activity_id分享私密消息。openid与unionid填一个即可。私密消息暂不支持云函数生成activity
     *                             id
     * @param string|null $openid  为私密消息创建activity_id时，指定分享者为openid用户。其余用户不能用此activity_id分享私密消息。openid与unionid填一个即可。私密消息暂不支持云函数生成activity
     *                             id
     *
     * @return array
     * @throws Exception
     */
    public function createActivityId(?string $unionid, ?string $openid)
    {
        $data = [
            'unionid' => $unionid,
            'openid'  => $openid,
        ];
        return $this->post('cgi-bin/message/wxopen/activityid/create', $data);
    }

    /**
     * 动态消息，修改被分享的动态消息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/updatable-message/setUpdatableMsg.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setUpdatableMsg(array $data)
    {
        return $this->post('cgi-bin/message/wxopen/updatablemsg/send', $data);
    }

    /**
     * 下发小程序和公众号统一的服务消息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/uniform-message/sendUniformMessage.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function uniformSend(array $data)
    {
        return $this->post('cgi-bin/message/wxopen/template/uniform_send', $data);
    }

}