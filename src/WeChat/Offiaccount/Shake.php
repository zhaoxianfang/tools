<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;


/**
 * 揺一揺周边
 * Class Shake
 *
 * @package WeChat
 */
class Shake extends WeChatBase
{
    /**
     * 申请开通功能
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function register(array $data)
    {
        return $this->post("shakearound/account/register", $data);
    }

    /**
     * 查询审核状态
     *
     * @return array
     * @throws Exception
     */
    public function auditStatus()
    {
        return $this->get("shakearound/account/auditstatus");
    }

    /**
     * 申请设备ID
     *
     * @param string      $quantity     申请的设备ID的数量，单次新增设备超过500个，需走人工审核流程
     * @param string      $apply_reason 申请理由，不超过100个汉字或200个英文字母
     * @param null|string $comment      备注，不超过15个汉字或30个英文字母
     * @param null|string $poi_id       设备关联的门店ID，关联门店后，在门店1KM的范围内有优先摇出信息的机会。
     *
     * @return array
     * @throws Exception
     */
    public function createApply($quantity, $apply_reason, $comment = null, $poi_id = null)
    {
        $data = ["quantity" => $quantity, "apply_reason" => $apply_reason];
        is_null($poi_id) || $data["poi_id"] = $poi_id;
        is_null($comment) || $data["comment"] = $comment;

        return $this->post("shakearound/device/applyid", $data);
    }

    /**
     * 查询设备ID申请审核状态
     *
     * @param integer $applyId 批次ID，申请设备ID时所返回的批次ID
     *
     * @return array
     * @throws Exception
     */
    public function getApplyStatus($applyId)
    {
        return $this->post("shakearound/device/applyid", ["apply_id" => $applyId]);
    }

    /**
     * 编辑设备信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateApply(array $data)
    {
        return $this->post("shakearound/device/update", $data);
    }

    /**
     * 配置设备与门店的关联关系
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function bindLocation(array $data)
    {
        return $this->post("shakearound/device/bindlocation", $data);
    }

    /**
     * 查询设备列表
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function search(array $data)
    {
        return $this->post("shakearound/device/search", $data);
    }

    /**
     * 页面管理
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function createPage(array $data)
    {
        return $this->post("shakearound/page/add", $data);
    }

    /**
     * 编辑页面信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePage(array $data)
    {
        return $this->post("shakearound/page/update", $data);
    }

    /**
     * 查询页面列表
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function searchPage(array $data)
    {
        return $this->post("shakearound/page/search", $data);
    }

    /**
     * 删除页面
     *
     * @param integer $page_id 指定页面的id
     *
     * @return array
     * @throws Exception
     */
    public function deletePage($page_id)
    {
        return $this->post("shakearound/page/delete", ["page_id" => $page_id]);
    }

    /**
     * 上传图片素材
     *
     * @param string $filename 图片名字
     * @param string $type     Icon：摇一摇页面展示的icon图；License：申请开通摇一摇周边功能时需上传的资质文件；若不传type，则默认type=icon
     *
     * @return array
     * @throws Exception
     */
    public function uploadIcon($filename, $type = "icon")
    {
        return $this->customUpload("shakearound/material/add", $filename, ["type" => $type]);
    }

    /**
     * 配置设备与页面的关联关系
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function bindPage(array $data)
    {
        return $this->post("shakearound/device/bindpage", $data);
    }

    /**
     * 查询设备与页面的关联关系
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function queryPage(array $data)
    {
        return $this->post("shakearound/relation/search", $data);
    }

    /**
     * 以设备为维度的数据统计接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function totalDevice(array $data)
    {
        return $this->post("shakearound/statistics/device", $data);
    }

    /**
     * 批量查询设备统计数据接口
     *
     * @param integer $date       指定查询日期时间戳，单位为秒
     * @param integer $page_index 指定查询的结果页序号；返回结果按摇周边人数降序排序，每50条记录为一页
     *
     * @return array
     * @throws Exception
     */
    public function totalDeviceList($date, $page_index = 1)
    {
        return $this->post("shakearound/statistics/devicelist", ["date" => $date, "page_index" => $page_index]);
    }

    /**
     * 以页面为维度的数据统计接口
     *
     * @param integer $page_id    指定页面的设备ID
     * @param integer $begin_date 起始日期时间戳，最长时间跨度为30天，单位为秒
     * @param integer $end_date   结束日期时间戳，最长时间跨度为30天，单位为秒
     *
     * @return array
     * @throws Exception
     */
    public function totalPage($page_id, $begin_date, $end_date)
    {
        return $this->post("shakearound/statistics/page", ["page_id" => $page_id, "begin_date" => $begin_date, "end_date" => $end_date]);
    }

    /**
     * 编辑分组信息
     *
     * @param integer $group_id   分组唯一标识，全局唯一
     * @param string  $group_name 分组名称，不超过100汉字或200个英文字母
     *
     * @return array
     * @throws Exception
     */
    public function updateGroup($group_id, $group_name)
    {
        return $this->post("shakearound/device/group/update", ["group_id" => $group_id, "group_name" => $group_name]);
    }

    /**
     * 删除分组
     *
     * @param integer $group_id 分组唯一标识，全局唯一
     *
     * @return array
     * @throws Exception
     */
    public function deleteGroup($group_id)
    {
        return $this->post("shakearound/device/group/delete", ["group_id" => $group_id]);
    }

    /**
     * 查询分组列表
     *
     * @param integer $begin 分组列表的起始索引值
     * @param integer $count 待查询的分组数量，不能超过1000个
     *
     * @return array
     * @throws Exception
     */
    public function getGroupList($begin = 0, $count = 10)
    {
        return $this->post("shakearound/device/group/getlist", ["begin" => $begin, "count" => $count]);
    }


    /**
     * 查询分组详情
     *
     * @param integer $group_id 分组唯一标识，全局唯一
     * @param integer $begin    分组里设备的起始索引值
     * @param integer $count    待查询的分组里设备的数量，不能超过1000个
     *
     * @return array
     * @throws Exception
     */
    public function getGroupDetail($group_id, $begin = 0, $count = 100)
    {
        return $this->post("shakearound/device/group/getdetail", ["group_id" => $group_id, "begin" => $begin, "count" => $count]);
    }

    /**
     * 添加设备到分组
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addDeviceGroup(array $data)
    {
        return $this->post("shakearound/device/group/adddevice", $data);
    }

    /**
     * 从分组中移除设备
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function deleteDeviceGroup(array $data)
    {
        return $this->post("shakearound/device/group/deletedevice", $data);
    }

}