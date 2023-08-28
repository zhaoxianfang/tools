<?php

namespace zxf\WeChat\MiniProgram\Live;

use Exception;

/**
 * 直播间主播副号
 */
class LiveRoomSubAnchor extends LiveBase
{
    /**
     * 获取主播副号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/getSubAnchor.html
     *
     * @param int $roomId
     *
     * @return array
     * @throws Exception
     */
    public function info(int $roomId)
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->get("wxaapi/broadcast/room/getsubanchor", $params);
        if ($res["errcode"] != 0) {
            return $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "获取成功",
            "code"    => $res["errcode"],
            "data"    => [
                "username" => $res["username"],
            ],
        ];
    }

    /**
     * 添加主播副号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/addSubAnchor.html
     *
     * @param int    $roomId
     * @param string $openId 用户微信号
     *
     * @return array
     * @throws Exception
     */
    public function add(int $roomId, string $openId)
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
        ];

        $res = $this->post("wxaapi/broadcast/room/addsubanchor", $params);
        if ($res["errcode"] != 0) {
            return $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "添加成功",
            "code"    => $res["errcode"],
        ];
    }

    /**
     * 修改主播副号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/modifySubAnchor.html
     *
     * @param int    $roomId 房间ID
     * @param string $openId 微信号
     *
     * @return array
     * @throws Exception
     */
    public function modify(int $roomId, string $openId)
    {
        $params = [
            "roomId"   => $roomId,
            "username" => $openId, // 用户微信号
        ];

        $res = $this->post("wxaapi/broadcast/room/modifysubanchor", $params);
        if ($res["errcode"] != 0) {
            return $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "修改成功",
            "code"    => $res["errcode"],
        ];
    }

    /**
     * 删除主播副号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/deleteSubAnchor.html
     *
     * @param int $roomId
     *
     * @return array
     * @throws Exception
     */
    public function delete(int $roomId)
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->post("wxaapi/broadcast/room/deletesubanchor", $params);
        if ($res["errcode"] != 0) {
            return $this->error($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "删除成功",
            "code"    => $res["errcode"],
        ];
    }
}
