<?php

namespace zxf\WeChat\MiniProgram\Live;

use Exception;

/**
 * 直播长期订阅
 */
class LiveSubscribe extends LiveBase
{
    // 获取长期订阅用户
    public function list(int $page = 1, int $limit = 200)
    {
        if ($page <= 1) {
            return $this->getWxaFollowers(0, $limit);
        } else {
            $res = $this->getWxaFollowers(0, max(($page - 1), 1) * $limit);

            return $this->getWxaFollowers($res['data']['page_break'], $limit);
        }
    }

    /**
     * 长期订阅群发接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/subscribe-management/pushMessage.html
     *
     * @param  array  $openIds  接收该群发开播事件的订阅用户OpenId列表
     * @return array|mixed
     *
     * @throws Exception
     */
    public function pushMessage(int $roomId, array $openIds = [])
    {
        $params = [
            'room_id' => $roomId,
            'user_openid' => $openIds,
        ];

        $res = $this->get('wxa/business/push_message', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
            'data' => [
                'message_id' => $res['message_id'],
            ],
        ];
    }

    // 长期订阅群发结果回调
    public function callback()
    {
        return $this->error('长期订阅群发结果回调', 403);
    }

    /**
     * 获取长期订阅用户
     *
     * @param  int|null  $page_break  翻页标记，获取第一页时不带，第二页开始需带上上一页返回结果中的page_break
     * @param  int|null  $limit  获取长期订阅用户的个数限制，默认200，最大2000
     * @return array|mixed
     *
     * @throws Exception
     */
    public function getWxaFollowers(?int $page_break = 0, ?int $limit = 200)
    {
        $params = [
            'limit' => $limit,
            'page_break' => $page_break, // 翻页标记，获取第一页时不带，第二页开始需带上上一页返回结果中的page_break
        ];

        $res = $this->get('wxa/business/get_wxa_followers', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => '获取成功',
            'code' => $res['errcode'],
            'data' => [
                'page_break' => $res['page_break'],
                'list' => $res['followers'],
            ],
        ];
    }
}
