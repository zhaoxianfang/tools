<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信小程序地址管理
 * Class Poi
 *
 * @package WeMini
 */
class Poi extends WeChatBase
{
    /**
     * 添加地点
     *
     * @param string $related_name           经营资质主体
     * @param string $related_credential     经营资质证件号
     * @param string $related_address        经营资质地址
     * @param string $related_proof_material 相关证明材料照片临时素材mediaid
     *
     * @return array
     * @throws Exception
     */
    public function addBearByPoi($related_name, $related_credential, $related_address, $related_proof_material)
    {
        $data = [
            "related_name"    => $related_name, "related_credential" => $related_credential,
            "related_address" => $related_address, "related_proof_material" => $related_proof_material,
        ];
        return $this->post("wxa/addnearbypoi", $data);
    }

    /**
     * 查看地点列表
     *
     * @param integer $page      起始页id（从1开始计数）
     * @param integer $page_rows 每页展示个数（最多1000个）
     *
     * @return array
     * @throws Exception
     */
    public function getNearByPoiList($page = 1, $page_rows = 1000)
    {
        return $this->get("wxa/getnearbypoilist", [], [
            "page"      => $page,
            "page_rows" => $page_rows,
        ]);
    }

    /**
     * 删除地点
     *
     * @param string $poi_id 附近地点ID
     *
     * @return array
     * @throws Exception
     */
    public function delNearByPoiList($poi_id)
    {
        return $this->post("wxa/delnearbypoi", ["poi_id" => $poi_id]);
    }

    /**
     * 展示/取消展示附近小程序
     *
     * @param string $poi_id 附近地点ID
     * @param string $status 0：取消展示；1：展示
     *
     * @return array
     * @throws Exception
     */
    public function setNearByPoiShowStatus($poi_id, $status)
    {
        return $this->post("wxa/setnearbypoishowstatus", ["poi_id" => $poi_id, "status" => $status], true);
    }
}