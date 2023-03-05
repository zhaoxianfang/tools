<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序直播接口
 * Class Live
 *
 * @package WeMini
 */
class Live extends WeChatBase
{
    /**
     * 创建直播间
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        return $this->post("wxaapi/broadcast/room/create", $data);
    }

    /**
     * 获取直播房间列表
     *
     * @param integer $start 起始拉取房间
     * @param integer $limit 每次拉取的个数上限
     *
     * @return array
     * @throws Exception
     */
    public function getLiveList($start = 0, $limit = 10)
    {
        return $this->post("wxa/business/getliveinfo", ["start" => $start, "limit" => $limit], true);
    }

    /**
     * 获取回放源视频
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getLiveInfo($data = [])
    {
        return $this->post("wxa/business/getliveinfo", $data);
    }

    /**
     * 直播间导入商品
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addLiveGoods($data = [])
    {
        return $this->post("wxaapi/broadcast/room/addgoods", $data);
    }

    /**
     * 商品添加并提审
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/add", $data);
    }

    /**
     * 商品撤回审核
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function resetAuditGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/resetaudit", $data);
    }

    /**
     * 重新提交审核
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function auditGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/audit", $data);
    }

    /**
     * 删除商品
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function deleteGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/delete", $data);
    }

    /**
     * 更新商品
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/update", $data);
    }

    /**
     * 获取商品状态
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function stateGoods($data)
    {
        return $this->post("wxa/business/getgoodswarehouse", $data);
    }

    /**
     * 获取商品列表
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGoods($data)
    {
        return $this->post("wxaapi/broadcast/goods/getapproved", $data);
    }
}