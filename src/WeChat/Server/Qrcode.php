<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\Server\Common\BasicWeChat;
use Exception;

/**
 * 生成带参数的二维码
 * Class Qrcode
 *
 */
class Qrcode extends BasicWeChat
{

    /**
     * 创建二维码ticket
     *
     * @param string|integer $scene          场景值（数字类型与字符串类型自动转换）
     * @param int            $expire_seconds 有效时间（可选，有值时为临时二维码）
     *
     * @return array
     * @throws Exception
     */
    public function create($scene, $expire_seconds = 0)
    {
        if (is_integer($scene)) { // 二维码场景类型
            $data = ['action_info' => ['scene' => ['scene_id' => $scene]]];
        } else {
            $data = ['action_info' => ['scene' => ['scene_str' => $scene]]];
        }
        if ($expire_seconds > 0) { // 临时二维码
            $data['expire_seconds'] = $expire_seconds;
            $data['action_name']    = is_integer($scene) ? 'QR_SCENE' : 'QR_STR_SCENE';
        } else { // 永久二维码
            $data['action_name'] = is_integer($scene) ? 'QR_LIMIT_SCENE' : 'QR_LIMIT_STR_SCENE';
        }
        return $this->post('cgi-bin/qrcode/create', $data);
    }

    /**
     * 通过ticket换取二维码
     *
     * @param string $ticket 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     *
     * @return string
     */
    public function url($ticket)
    {
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
    }

    /**
     * 长链接转短链接接口
     *
     * @param string $longUrl 需要转换的长链接
     *
     * @return array
     * @throws Exception
     */
    public function shortUrl($longUrl)
    {
        return $this->post('cgi-bin/shorturl', ['action' => 'long2short', 'long_url' => $longUrl]);
    }
}