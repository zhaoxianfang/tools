<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

class Script extends WeChatBase
{
    public $useToken = true;

    /**
     * 删除JSAPI授权TICKET
     *
     * @param string      $type  TICKET类型(wx_card|jsapi)
     * @param string|null $appid 强制指定有效APPID
     *
     * @return void
     */
    public function delTicket(string $type = 'jsapi', ?string $appid = null)
    {
        is_null($appid) && $appid = $this->config->get('appid');
        $cache_name = "{$appid}_ticket_{$type}";
        $this->cache->delete($cache_name);
    }

    /**
     * 获取JSAPI_TICKET接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WeChat_Invoice/E_Invoice/Vendor_API_List.html#1
     *
     * @param string      $type  TICKET类型(wx_card|jsapi)
     * @param string|null $appid 强制指定有效APPID
     *
     * @return string
     * @throws Exception
     */
    public function getTicket(string $type = 'jsapi', ?string $appid = null)
    {
        is_null($appid) && $appid = $this->config->get('appid');
        $cache_name = "{$appid}_ticket_{$type}";
        $ticket     = $this->cache->get($cache_name, []);
        if (empty($ticket)) {
            $result = $this->get('cgi-bin/ticket/getticket', [], ['type' => $type]);
            if (empty($result['ticket'])) {
                $this->error('Invalid Resoponse Ticket.');
            }
            $ticket = $result['ticket'];
            $this->cache->set($cache_name, $ticket, 7000);
        }
        return $ticket;
    }

    /**
     * 获取JsApi使用签名
     *
     * @param string      $url       网页的URL
     * @param string|null $appid     用于多个appid时使用(可空)
     * @param string|null $ticket    强制指定ticket
     * @param array|null  $jsApiList 需初始化的 jsApiList
     *
     * @return array
     * @throws Exception
     */
    public function getJsSign(string $url, ?string $appid = null, ?string $ticket = null, ?array $jsApiList = null)
    {
        list($url,) = explode('#', $url);
        is_null($ticket) && $ticket = $this->getTicket('jsapi');
        is_null($appid) && $appid = $this->config->get('appid');
        is_null($jsApiList) && $jsApiList = [
            'updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
            'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
            'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
            'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
            'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
        ];
        $data = ["url" => $url, "timestamp" => '' . time(), "jsapi_ticket" => $ticket, "noncestr" => $this->createNoncestr(16)];
        return [
            'debug'     => false,
            "appId"     => $appid,
            "nonceStr"  => $data['noncestr'],
            "timestamp" => $data['timestamp'],
            "signature" => $this->getSignature($data, 'sha1'),
            'jsApiList' => $jsApiList,
        ];
    }

    /**
     * 数据生成签名
     *
     * @param array  $data   签名数组
     * @param string $method 签名方法
     * @param array  $params 签名参数
     *
     * @return bool|string 签名值
     */
    protected function getSignature(array $data, string $method = "sha1", array $params = [])
    {
        ksort($data);
        if (!function_exists($method)) {
            return false;
        }
        foreach ($data as $k => $v) {
            $params[] = "{$k}={$v}";
        }
        return $method(join('&', $params));
    }

    /**
     * 产生随机字符串
     *
     * @param int    $length 指定字符长度
     * @param string $str    字符串前缀
     *
     * @return string
     */
    private function createNoncestr($length = 32, $str = "")
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}