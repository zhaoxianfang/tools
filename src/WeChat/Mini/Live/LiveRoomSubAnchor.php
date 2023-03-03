<?php

namespace zxf\WeChat\Mini\Live;

/**
 * 直播间主播副号
 */
class LiveRoomSubAnchor extends LiveBase
{
    // 获取主播副号
    public function info($roomId)
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->get('wxaapi/broadcast/room/getsubanchor', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return [
            'message' => '获取成功',
            'code'    => $res['errcode'],
            'data'    => [
                'username' => $res['username'],
            ],
        ];
    }

    // 添加主播副号
    public function add($roomId, $openId)
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
        ];

        $res = $this->post('wxaapi/broadcast/room/addsubanchor', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return [
            'message' => '添加成功',
            'code'    => $res['errcode'],
        ];
    }

    // 修改主播副号
    public function modify($roomId, $openId)
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
        ];

        $res = $this->post('wxaapi/broadcast/room/modifysubanchor', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return [
            'message' => '修改成功',
            'code'    => $res['errcode'],
        ];
    }

    // 删除主播副号
    public function delete($roomId)
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->post('wxaapi/broadcast/room/deletesubanchor', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return [
            'message' => '删除成功',
            'code'    => $res['errcode'],
        ];
    }
}
