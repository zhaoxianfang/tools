<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 小程序客服
 */
class Custom extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取客服消息内的临时素材
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/kf-mgnt/kf-message/getTempMedia.html
     *
     * @param string $media_id 媒体文件 ID。可通过uploadTempMedia接口获得media_id
     *
     * @return array
     * @throws Exception
     */
    public function getTempMedia(string $media_id)
    {
        return $this->post('cgi-bin/media/get', ['media_id' => $media_id]);
    }

    /**
     * 下发客服当前输入状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/kf-mgnt/kf-message/setTyping.html
     *
     * @param string $touser  用户的 OpenID
     * @param string $command 命令。Typing表示对用户下发"正在输入"状态 ；CancelTyping表示取消对用户的"正在输入"状态
     *
     * @return array
     * @throws Exception
     */
    public function setTyping(string $touser, string $command = 'Typing')
    {
        return $this->post('cgi-bin/message/custom/business/typing', ['touser' => $touser, 'command' => $command]);
    }

    /**
     * 新增图片素材
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/kf-mgnt/kf-message/uploadTempMedia.html
     *
     * @param string $type 文件类型,可填“ image”，表示图片
     * @param string $filePath
     *
     * @return array
     * @throws Exception
     */
    public function uploadTempMedia(string $type, string $filePath)
    {
        return $this->httpUpload('cgi-bin/media/upload', $filePath, ['type' => $type]);
    }

    /**
     * 发送客服消息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/kf-mgnt/kf-message/sendCustomMessage.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function sendCustomMessage(array $data)
    {
        return $this->post('cgi-bin/message/custom/send', $data);
    }

}