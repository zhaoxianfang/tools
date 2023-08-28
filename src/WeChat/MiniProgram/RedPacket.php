<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

class RedPacket extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取微信红包封面
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/red-packet-cover/getRedPacketCoverUrl.html
     *
     * @param string $openid 可领取用户的openid
     * @param string $ctoken 在红包封面平台获取发放ctoken（需要指定可以发放的appid）
     *
     * @return array
     * @throws Exception
     */
    public function getRedPacketCoverUrl(string $openid, string $ctoken)
    {
        return $this->post('redpacketcover/wxapp/cover_url/get_by_token', [
            'openid' => $openid,
            'ctoken' => $ctoken,
        ]);
    }
}