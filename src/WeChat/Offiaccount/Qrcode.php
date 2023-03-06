<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 生成带参数的二维码
 * Class Qrcode
 *
 */
class Qrcode extends WeChatBase
{

    /**
     * 创建二维码ticket
     *
     * @param string|integer $scene          场景值（数字类型与字符串类型自动转换）
     * @param int            $expire_seconds 有效时间（可选，有值时为临时二维码） 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为60秒。
     *
     * @return array
     * @throws Exception
     */
    public function create($scene, $expire_seconds = 0)
    {
        // scene_id：场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
        // scene_str：场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
        if (is_integer($scene)) { // 二维码场景类型
            $data = ["action_info" => ["scene" => ["scene_id" => $scene]]];
        } else {
            $data = ["action_info" => ["scene" => ["scene_str" => $scene]]];
        }
        if ($expire_seconds > 0) { // 临时二维码
            $data["expire_seconds"] = $expire_seconds;
            $data["action_name"]    = is_integer($scene) ? "QR_SCENE" : "QR_STR_SCENE";
        } else { // 永久二维码
            $data["action_name"] = is_integer($scene) ? "QR_LIMIT_SCENE" : "QR_LIMIT_STR_SCENE";
        }
        return $this->post("cgi-bin/qrcode/create", $data);
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
        return sprintf("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s", urlencode($ticket));
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
        return $this->post("cgi-bin/shorturl", ["action" => "long2short", "long_url" => $longUrl]);
    }

    /**
     * 短key托管
     *
     * @param string $longData      需要转换的长信息，不超过4KB
     * @param int    $expireSeconds 过期秒数，最大值为2592000（即30天），默认为2592000
     *
     * @return mixed
     * @throws Exception
     */
    public function genShorten(string $longData = '', int $expireSeconds = 2592000)
    {
        return $this->post("cgi-bin/shorten/gen", ["long_data" => $longData, "expire_seconds" => $expireSeconds]);
    }
}