<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信小程序地址管理(附近小程序)
 */
class Poi extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 添加地点(加附近小程序的地点)
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/nearby-poi/addNearbyPoi.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function addBearByPoi(array $data)
    {
        return $this->post('wxa/addnearbypoi', $data);
    }

    /**
     * 查看地点列表 - 查看附近小程序的地点列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/nearby-poi/getNearbyPoiList.html
     *
     * @param  int  $page  起始页id（从1开始计数）
     * @param  int  $page_rows  每页展示个数（最多1000个）
     * @return array
     *
     * @throws Exception
     */
    public function getNearByPoiList(int $page = 1, int $page_rows = 1000)
    {
        return $this->get('wxa/getnearbypoilist', [], ['page' => $page, 'page_rows' => $page_rows]);
    }

    /**
     * 删除地点(删除附近小程序的地点)
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/nearby-poi/deleteNearbyPoi.html
     *
     * @param  string  $poi_id  附近地点ID
     * @return array
     *
     * @throws Exception
     */
    public function delNearByPoiList(string $poi_id)
    {
        return $this->post('wxa/delnearbypoi', ['poi_id' => $poi_id]);
    }

    /**
     * 展示/取消展示附近小程序
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/nearby-poi/setShowStatus.html
     *
     * @param  string  $poi_id  附近地点ID
     * @param  int  $status  0：取消展示；1：展示
     * @return array
     *
     * @throws Exception
     */
    public function setNearByPoiShowStatus(string $poi_id, int $status)
    {
        return $this->post('wxa/setnearbypoishowstatus', ['poi_id' => $poi_id, 'status' => $status]);
    }
}
