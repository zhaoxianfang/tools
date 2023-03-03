<?php

namespace zxf\WeChat\Mini\Live;

class LiveRoomAssistant extends LiveBase
{

    // 添加管理直播间小助手
    public function add($roomId, $openId, $nickname = ''): array
    {
        $params = [
            "roomId" => $roomId,
            'users'  => [
                [
                    "username" => $openId, // 用户微信号
                    "nickname" => $nickname, // 用户昵称
                ],
            ],
        ];

        $res = $this->get('wxaapi/broadcast/room/addassistant', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '添加成功',
            'code'    => $res['errcode'],
        ];
    }

    // 修改管理直播间小助手
    public function modify($roomId, $openId, $nickname = ''): array
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
            "nickname" => $nickname, // 用户昵称
        ];

        $res = $this->post('wxaapi/broadcast/room/modifyassistant', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '修改成功',
            'code'    => $res['errcode'],
        ];
    }

    // 删除管理直播间小助手
    public function remove($roomId, $openId): array
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
        ];

        $res = $this->post('wxaapi/broadcast/room/removeassistant', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '删除成功',
            'code'    => $res['errcode'],
        ];
    }

    // 查询管理直播间小助手
    public function info($roomId): array
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->get('wxaapi/broadcast/room/getassistantlist', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '获取成功',
            'code'    => $res['errcode'],
            'data'    => [
                'count'    => $res['count'],
                'maxCount' => $res['maxCount'],
                'list'     => $res['list'],
            ],
        ];
    }
}
