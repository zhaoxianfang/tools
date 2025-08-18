<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 二维码管理
 */
class Qrcode extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 创建二维码ticket
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
     *
     * @param  string|int  $scene  自定场景
     * @param  int  $expire_seconds  该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为60秒。
     * @return array
     *
     * @throws Exception
     */
    public function create($scene, $expire_seconds = 0)
    {
        if (is_int($scene)) { // 二维码场景类型
            $data = ['action_info' => ['scene' => ['scene_id' => $scene]]];
        } else {
            $data = ['action_info' => ['scene' => ['scene_str' => $scene]]];
        }
        if ($expire_seconds > 0) { // 临时二维码
            $data['expire_seconds'] = $expire_seconds;
            $data['action_name'] = is_int($scene) ? 'QR_SCENE' : 'QR_STR_SCENE';
        } else { // 永久二维码
            $data['action_name'] = is_int($scene) ? 'QR_LIMIT_SCENE' : 'QR_LIMIT_STR_SCENE';
        }

        return $this->post('cgi-bin/qrcode/create', $data);
    }

    /**
     * 通过ticket换取二维码
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
     *
     * @param  string  $ticket  获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     * @return string
     */
    public function url(string $ticket)
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
    }

    /**
     * 长链接转短链接接口
     *
     * @link https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/qrcode/shorturl.html
     *
     * @param  string  $longUrl  需要转换的长链接
     * @return array
     *
     * @throws Exception
     */
    public function shortUrl($longUrl)
    {
        return $this->post('cgi-bin/shorturl', ['action' => 'long2short', 'long_url' => $longUrl]);
    }
}
