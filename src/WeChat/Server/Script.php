<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信前端支持
 * Class Script
 *
 * @package WeChat
 */
class Script extends WeChatBase
{

    /**
     * 删除JSAPI授权TICKET
     *
     * @param string $type  TICKET类型(wx_card|jsapi)
     * @param string $appid 强制指定有效APPID
     *
     * @return void
     */
    public function delTicket($type = 'jsapi', $appid = null)
    {
        is_null($appid) && $appid = $this->config['appid'];
        $cache_name = "{$appid}_ticket_{$type}";
        $this->cache->delete($cache_name);
    }

    /**
     * 获取JSAPI_TICKET接口
     *
     * @param string $type  TICKET类型(wx_card|jsapi)
     * @param string $appid 强制指定有效APPID
     *
     * @return string
     * @throws Exception
     */
    public function getTicket($type = 'jsapi', $appid = null)
    {
        is_null($appid) && $appid = $this->config['appid'];
        $cache_name = "{$appid}_ticket_{$type}";
        $ticket     = $this->cache->get($cache_name);
        if (empty($ticket)) {
            $result = $this->get("cgi-bin/ticket/getticket", [], ['type' => $type]);
            if (empty($result['ticket'])) {
                throw new Exception('Invalid Resoponse Ticket.', '0');
            }
            $ticket = $result['ticket'];
            $this->cache->set($cache_name, $ticket, 7000);
        }
        return $ticket;
    }

    /**
     * 获取JsApi使用签名
     *
     * @param string $url       网页的URL
     * @param string $appid     用于多个appid时使用(可空)
     * @param string $ticket    强制指定ticket
     * @param array  $jsApiList 需初始化的 jsApiList
     *
     * @return array
     * @throws Exception
     */
    public function getJsSign($url, $appid = null, $ticket = null, $jsApiList = null)
    {
        list($url,) = explode('#', $url);
        is_null($ticket) && $ticket = $this->getTicket('jsapi');
        is_null($appid) && $appid = $this->config['appid'];
        is_null($jsApiList) && $jsApiList = [
            'updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
            'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
            'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
            'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
            'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
        ];
        $data = ["url" => $url, "timestamp" => '' . time(), "jsapi_ticket" => $ticket, "noncestr" => \zxf\Facade\Random::alnum(16)];
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
    protected function getSignature($data, $method = "sha1", $params = [])
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
}