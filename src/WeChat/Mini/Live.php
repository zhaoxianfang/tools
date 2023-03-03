<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序直播接口
 * Class Live
 * @package WeMini
 */
class Live extends WeChatBase
{
    /**
     * 创建直播间
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        $url = 'wxaapi/broadcast/room/create';
        return $this->post($url, $data);
    }

    /**
     * 获取直播房间列表
     * @param integer $start 起始拉取房间
     * @param integer $limit 每次拉取的个数上限
     * @return array
     * @throws Exception
     */
    public function getLiveList($start = 0, $limit = 10)
    {
        $url = 'wxa/business/getliveinfo';
        return $this->callPostApi($url, ['start' => $start, 'limit' => $limit], true);
    }

    /**
     * 获取回放源视频
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getLiveInfo($data = [])
    {
        $url = 'wxa/business/getliveinfo';
        return $this->post($url, $data);
    }

    /**
     * 直播间导入商品
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addLiveGoods($data = [])
    {
        $url = 'wxaapi/broadcast/room/addgoods';
        return $this->post($url, $data);
    }

    /**
     * 商品添加并提审
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGoods($data)
    {
        $url = "wxaapi/broadcast/goods/add";
        return $this->post($url, $data);
    }

    /**
     * 商品撤回审核
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function resetAuditGoods($data)
    {
        $url = "wxaapi/broadcast/goods/resetaudit";
        return $this->post($url, $data);
    }

    /**
     * 重新提交审核
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function auditGoods($data)
    {
        $url = "wxaapi/broadcast/goods/audit";
        return $this->post($url, $data);
    }

    /**
     * 删除商品
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function deleteGoods($data)
    {
        $url = "wxaapi/broadcast/goods/delete";
        return $this->post($url, $data);
    }

    /**
     * 更新商品
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateGoods($data)
    {
        $url = "wxaapi/broadcast/goods/update";
        return $this->post($url, $data);
    }

    /**
     * 获取商品状态
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function stateGoods($data)
    {
        $url = "wxa/business/getgoodswarehouse";
        return $this->post($url, $data);
    }

    /**
     * 获取商品列表
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGoods($data)
    {
        $url = "wxaapi/broadcast/goods/getapproved";
        return $this->post($url, $data);
    }
}