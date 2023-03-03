<?php

namespace zxf\WeChat\Mini\Live;

/**
 * 直播间内的商品管理
 */
class LiveRoomGoods extends LiveBase
{

    // 直播间导入商品
    public function import($roomId, array $goodsIdsArr = [])
    {

        $params = [
            "ids"    => $goodsIdsArr,  // 数组列表，可传入多个，里面填写 商品 ID
            "roomId" => $roomId,
        ];
        $res    = $this->post('wxaapi/broadcast/room/addgoods', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '导入成功',
            'code'    => $res['errcode'],
        ];
    }

    // 上下架商品
    public function onSale($roomId, $goodsId, $onSale)
    {
        $params = [
            "roomId"  => $roomId,
            "goodsId" => $goodsId,
            "onSale"  => $onSale,
        ];

        $res = $this->post('wxaapi/broadcast/goods/onsale', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return [
            'message' => '操作成功',
            'code'    => $res['errcode'],
        ];
    }

    // 删除商品
    public function delete($roomId, $goodsId)
    {
        $params = [
            "roomId"  => $roomId,
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/deleteInRoom', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '操作成功',
            'code'    => $res['errcode'],
        ];
    }

    // 推送商品
    public function push($roomId, $goodsId)
    {
        $params = [
            "roomId"  => $roomId,
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/push', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '操作成功',
            'code'    => $res['errcode'],
        ];

    }

    // 商品排序
    public function sort($roomId, array $goodsIds = [])
    {
        $goods = [];
        foreach ($goodsIds as $id) {
            $goods[] = ['goodsId' => $id];
        }
        $params = [
            "roomId" => $roomId,
            "goods"  => $goods,
        ];

        $res = $this->post('wxaapi/broadcast/goods/sort', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '操作成功',
            'code'    => $res['errcode'],
        ];
    }

    // 下载商品讲解视频
    public function getVideo($roomId, $goodsId)
    {
        $params = [
            "roomId"  => $roomId,
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/getVideo', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getMessage($res['errcode']));
        }
        return [
            'message' => '操作成功',
            'code'    => $res['errcode'],
            'data'    => [
                'url' => $res['url'],
            ],
        ];
    }
}
