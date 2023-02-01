<?php

namespace zxf\WeChat\Live;

class LiveRole extends Live
{
    // 设置成员角色
    public function add($openId, $role = 2): array
    {
        $params = [
            "username" => $openId,// 微信号
            "role"     => $role,// 取值[1-管理员，2-主播，3-运营者]，设置超级管理员将无效
        ];

        $res = $this->post('wxaapi/broadcast/role/addrole', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
        ];
    }

    // 解除成员角色
    public function delete($openId, $role = 2): array
    {
        $params = [
            "username" => $openId,// 微信号
            "role"     => $role,// 取值[1-管理员，2-主播，3-运营者]，删除超级管理员将无效
        ];

        $res = $this->post('wxaapi/broadcast/role/deleterole', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
        ];
    }

    // 查询成员列表
    public function list($role = -1, $page = 1, $limit = 10, $keyword = ''): array
    {
        $offset = max($page - 1, 0) * $limit;
        $params = [
            "role"    => $role, // 取值 [-1-所有成员， 0-超级管理员，1-管理员，2-主播，3-运营者]
            "offset"  => $offset, // 起始偏移量
            "limit"   => $limit, // 查询个数，最大30，默认10
            "keyword" => $keyword // 搜索的微信号，不传返回全部
        ];

        $res = $this->post('wxaapi/broadcast/role/getrolelist', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
            'data'    => [
                'total' => $res['total'],
                'list'  => $res['list'],
            ],
        ];
    }
}
