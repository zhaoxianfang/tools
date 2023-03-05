<?php

namespace zxf\WeChat\Mini\Live;

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
            return $this->getWxaFollowers($res["data"]["page_break"], $limit);
        }
    }

    // 长期订阅群发接口
    public function pushMessage($roomId, array $openIds = [])
    {
        $params = [
            "room_id"     => $roomId,
            "user_openid" => $openIds,
        ];

        $res = $this->get("wxa/business/push_message", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
            "data"    => [
                "message_id" => $res["message_id"],
            ],
        ];
    }

    // 长期订阅群发结果回调
    public function callback()
    {
        throw new \Exception("长期订阅群发结果回调", 403);
    }

    private function getWxaFollowers($page_break = 0, $limit = 200)
    {
        $params = [
            "limit"      => $limit,
            "page_break" => $page_break //翻页标记，获取第一页时不带，第二页开始需带上上一页返回结果中的page_break
        ];

        $res = $this->get("wxa/business/get_wxa_followers", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "获取成功",
            "code"    => $res["errcode"],
            "data"    => [
                "page_break" => $res["page_break"],
                "list"       => $res["followers"],
            ],
        ];
    }
}
