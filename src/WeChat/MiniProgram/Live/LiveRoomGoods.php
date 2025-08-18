<?php

namespace zxf\WeChat\MiniProgram\Live;

use Exception;

/**
 * 直播间内的商品管理
 */
class LiveRoomGoods extends LiveBase
{
    /**
     * 直播间导入商品
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/importGoods.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function import(int $roomId, array $goodsIdsArr = [])
    {
        $params = [
            'ids' => $goodsIdsArr,  // 数组列表，可传入多个，里面填写 商品 ID
            'roomId' => $roomId,
        ];
        $res = $this->post('wxaapi/broadcast/room/addgoods', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return $res;
    }

    /**
     * 上下架商品
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/SaleGoods.html
     *
     * @param  int  $goodsId  商品ID
     * @param  int  $onSale  上下架 【0：下架，1：上架】
     * @return array
     *
     * @throws Exception
     */
    public function onSale(int $roomId, int $goodsId, int $onSale)
    {
        $params = [
            'roomId' => $roomId,
            'goodsId' => $goodsId,
            'onSale' => $onSale,
        ];

        $res = $this->post('wxaapi/broadcast/goods/onsale', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']), $res['errcode']);
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 删除直播间商品
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/deleteDoods.html
     *
     * @param  int  $goodsId  商品ID
     * @return array
     *
     * @throws Exception
     */
    public function delete(int $roomId, int $goodsId)
    {
        $params = [
            'roomId' => $roomId,
            'goodsId' => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/deleteInRoom', $params);
        if ($res['errcode'] != 0 && $res['errcode'] != 7000) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 推送商品
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/pushGoods.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function push(int $roomId, int $goodsId)
    {
        $params = [
            'roomId' => $roomId,
            'goodsId' => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/push', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
        ];

    }

    /**
     * 商品排序
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/sortGoods.html
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    public function sort(int $roomId, array $goodsIds = [])
    {
        $goods = [];
        foreach ($goodsIds as $id) {
            $goods[] = ['goodsId' => (string) $id];
        }
        $params = [
            'roomId' => $roomId,
            'goods' => $goods,
        ];

        $res = $this->post('wxaapi/broadcast/goods/sort', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
        ];
    }

    /**
     * 下载商品讲解视频
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/livebroadcast/studio-management/downloadGoodsVideo.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function getVideo(int $roomId, int $goodsId)
    {
        $params = [
            'roomId' => $roomId,
            'goodsId' => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/getVideo', $params);
        if ($res['errcode'] != 0) {
            return $this->error($this->getMessage($res['errcode']));
        }

        return [
            'message' => '操作成功',
            'code' => $res['errcode'],
            'data' => [
                'url' => $res['url'],
            ],
        ];
    }
}
