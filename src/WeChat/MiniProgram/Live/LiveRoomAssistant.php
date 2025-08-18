<?php

namespace zxf\WeChat\MiniProgram\Live;

use Exception;

class LiveRoomAssistant extends LiveBase
{
    /**
     * 添加管理直播间小助手
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/addveAssistant.html
     *
     * @param  array  $users  用户数组 [{"username":"testwechat","nickname":"testnick"}]
     *
     * @throws Exception
     */
    public function add(int $roomId, array $users): array
    {
        $params = [
            'roomId' => $roomId,
            'users' => $users,
        ];

        $res = $this->get('wxaapi/broadcast/room/addassistant', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '添加成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 修改管理直播间小助手
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/modifyAssistant.html
     *
     * @throws Exception
     */
    public function modify(int $roomId, string $openId, string $nickname = ''): array
    {
        $params = [
            'roomId' => $roomId,
            'username' => $openId, // 用户微信号
            'nickname' => $nickname, // 用户昵称
        ];

        $res = $this->post('wxaapi/broadcast/room/modifyassistant', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '修改成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 删除管理直播间小助手
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/removeAssistant.html
     *
     * @throws Exception
     */
    public function remove(int $roomId, string $openId): array
    {
        $params = [
            'roomId' => $roomId,
            'username' => $openId, // 用户微信号
        ];

        $res = $this->post('wxaapi/broadcast/room/removeassistant', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '删除成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 查询管理直播间小助手
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/getAssistantList.html
     *
     * @throws Exception
     */
    public function info(int $roomId): array
    {
        $params = [
            'roomId' => $roomId,
        ];

        $res = $this->get('wxaapi/broadcast/room/getassistantlist', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '获取成功',
            'code' => $res['errcode'],
            'data' => [
                'count' => $res['count'],
                'maxCount' => $res['maxCount'],
                'list' => $res['list'],
            ],
        ];
    }
}
