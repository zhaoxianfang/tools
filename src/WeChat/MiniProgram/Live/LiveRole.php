<?php

namespace zxf\WeChat\MiniProgram\Live;

use Exception;

class LiveRole extends LiveBase
{
    /**
     * 设置成员角色
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/role-management/getRoleListdw.html
     *
     * @param  int  $role  取值[1-管理员，2-主播，3-运营者]，设置超级管理员将无效
     *
     * @throws Exception
     */
    public function add(string $wechatName, int $role = 2): array
    {
        $params = [
            'username' => $wechatName, // 微信号
            'role' => $role, // 取值[1-管理员，2-主播，3-运营者]，设置超级管理员将无效
        ];

        $res = $this->post('wxaapi/broadcast/role/addrole', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => $this->getMessage($res['errcode']),
            'code' => $res['errcode'],
        ];
    }

    /**
     * 解除成员角色
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/role-management/deleteRole.html
     *
     * @param  string  $wechatName  微信号
     * @param  int  $role  取值[1-管理员，2-主播，3-运营者]，删除超级管理员将无效
     *
     * @throws Exception
     */
    public function delete(string $wechatName, int $role = 2): array
    {
        $params = [
            'username' => $wechatName, // 微信号
            'role' => $role, // 取值[1-管理员，2-主播，3-运营者]，删除超级管理员将无效
        ];

        $res = $this->post('wxaapi/broadcast/role/deleterole', $params);
        if ($res['errcode'] != 0 && $res['errcode'] != 2003) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => $this->getMessage($res['errcode']),
            'code' => $res['errcode'],
        ];
    }

    /**
     * 查询成员列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/role-management/getRoleList.html
     *
     * @param  int|null  $role  查询的用户角色，取值 [-1-所有成员， 0-超级管理员，1-管理员，2-主播，3-运营者]，默认-1
     * @param  string|null  $keyword  搜索的微信号或昵称，不传则返回全部
     * @param  int|null  $limit  查询个数，最大30，默认10
     *
     * @throws Exception
     */
    public function list(?int $role = -1, ?string $keyword = '', ?int $offset = 0, ?int $limit = 10): array
    {
        $params = [
            'role' => $role, // 取值 [-1-所有成员， 0-超级管理员，1-管理员，2-主播，3-运营者]
            'offset' => $offset, // 起始偏移量
            'limit' => $limit, // 查询个数，最大30，默认10
            'keyword' => $keyword, // 搜索的微信号，不传返回全部
        ];
        $res = $this->get('wxaapi/broadcast/role/getrolelist', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => $this->getMessage($res['errcode']),
            'code' => $res['errcode'],
            'data' => [
                'total' => $res['total'],
                'list' => $res['list'],
            ],
        ];
    }
}
