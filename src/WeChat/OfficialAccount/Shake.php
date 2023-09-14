<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 揺一揺周边
 */
class Shake extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 申请开通功能
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Apply/Application_for_opening_function.html
     *
     * @param string      $name                    是    联系人姓名，不超过20汉字或40个英文字母
     * @param string      $phone_number            是    联系人电话
     * @param string      $email                   是    联系人邮箱
     * @param string      $industry_id             是    平台定义的行业代号，具体请查看链接 行业代号
     * @param array       $qualification_cert_urls 是    相关资质文件的图片url，图片需先上传至微信侧服务器，用“素材管理-上传图片素材”接口上传图片，返回的图片URL再配置在此处；
     *                                             当不需要资质文件时，数组内可以不填写url
     * @param string|null $apply_reason            否    申请理由，不超过250汉字或500个英文字母
     *
     * @return array
     * @throws Exception
     */
    public function register(string $name, string $phone_number, string $email, string $industry_id, array $qualification_cert_urls, ?string $apply_reason = '')
    {
        $data = [
            'name'                    => $name,
            'phone_number'            => $phone_number,
            'email'                   => $email,
            'industry_id'             => $industry_id,
            'qualification_cert_urls' => $qualification_cert_urls,
            'apply_reason'            => $apply_reason,
        ];
        return $this->post('shakearound/account/register', $data);
    }

    /**
     * 查询审核状态
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Apply/Application_for_opening_function.html#%E6%9F%A5%E8%AF%A2%E5%AE%A1%E6%A0%B8%E7%8A%B6%E6%80%81
     *
     * @return array
     * @throws Exception
     */
    public function auditStatus()
    {
        return $this->get('shakearound/account/auditstatus');
    }

    /**
     * 申请设备ID
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Devices_management/Apply_device_ID.html
     *
     * @param string      $quantity     申请的设备ID的数量，单次新增设备超过500个，需走人工审核流程
     * @param string      $apply_reason 申请理由，不超过100个汉字或200个英文字母
     * @param null|string $comment      备注，不超过15个汉字或30个英文字母
     * @param null|string $poi_id       设备关联的门店ID，关联门店后，在门店1KM的范围内有优先摇出信息的机会。
     *
     * @return array
     * @throws Exception
     */
    public function createApply(string $quantity, string $apply_reason, ?string $comment = null, ?string $poi_id = null)
    {
        $data = ['quantity' => $quantity, 'apply_reason' => $apply_reason];
        is_null($poi_id) || $data['poi_id'] = $poi_id;
        is_null($comment) || $data['comment'] = $comment;
        return $this->post('shakearound/device/applyid', $data);
    }

    /**
     * 编辑设备信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Devices_management/Edit_device_information.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateApply(array $data)
    {
        return $this->post('shakearound/device/update', $data);
    }

    /**
     * 配置设备与门店的关联关系
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Devices_management/Configure_the_connected_relationship_between_the_device_and_the_store.html
     *
     * @param int   $poi_id            设备关联的门店ID，关联门店后，在门店1KM的范围内有优先摇出信息的机会。当值为0时，将清除设备已关联的门店ID。门店相关信息具体可 查看门店相关的接口文档
     * @param array $device_identifier 指定的设备ID
     *
     * @return array
     * @throws Exception
     */
    public function bindLocation(int $poi_id, array $device_identifier)
    {
        $data = [
            'poi_id'            => $poi_id,
            'device_identifier' => $device_identifier,
        ];
        return $this->post('shakearound/device/bindlocation', $data);
    }


    /**
     * 获取设备信息，包括UUID、major、minor，以及距离、openID等信息。
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Getting_Device_and_User_Information.html
     *
     * @param string      $ticket   摇周边业务的ticket，可在摇到的URL中得到，ticket生效时间为30分钟，每一次摇都会重新生成新的ticket
     * @param string|null $need_poi 是否需要返回门店poi_id，传1则返回，否则不返回；门店相关信息具体可 查看门店相关的接口文档
     *
     * @return array
     * @throws Exception
     */
    public function getShakeInfo(string $ticket, ?string $need_poi = '0')
    {
        $data = [
            'ticket'   => $ticket,
            'need_poi' => $need_poi,
        ];
        return $this->post('shakearound/user/getshakeinfo', $data);
    }

    /**
     * 查询设备列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Devices_management/Query_device_list.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function search(array $data)
    {
        return $this->post('shakearound/device/search', $data);
    }

    /**
     * 页面管理
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Pages_management/Page_management.html
     *
     * @param string      $title       是    在摇一摇页面展示的主标题，不超过6个汉字或12个英文字母
     * @param string      $description 是    在摇一摇页面展示的副标题，不超过7个汉字或14个英文字母
     * @param string      $icon_url    是    在摇一摇页面展示的图片。图片需先上传至微信侧服务器，用“素材管理-上传图片素材”接口上传图片，返回的图片URL再配置在此处
     * @param string|null $comment     否    页面的备注信息，不超过15个汉字或30个英文字母
     *
     * @return array
     * @throws Exception
     */
    public function createPage(string $title, string $description, string $icon_url, ?string $comment = null)
    {
        $data = [
            'title'       => $title,
            'description' => $description,
            'icon_url'    => $icon_url,
        ];
        is_null($comment) || $data['comment'] = $comment;
        return $this->post('shakearound/page/add', $data);
    }

    /**
     * 编辑页面信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Pages_management/Edit_page_information.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updatePage(array $data)
    {
        return $this->post('shakearound/page/update', $data);
    }

    /**
     * 查询页面列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Pages_management/Query_page_list.html
     *
     *
     * @param int        $type     是    查询类型。1： 查询页面id列表中的页面信息；2：分页查询所有页面信息
     * @param array|null $page_ids 是    指定页面的id列表；当type为1时，此项为必填
     * @param int|null   $begin    是    页面列表的起始索引值；当type为2时，此项为必填
     * @param int|null   $count    是    待查询的页面数量，不能超过50个；当type为2时，此项为必填
     *
     * @return mixed
     * @throws Exception
     */
    public function searchPage(int $type, ?array $page_ids, ?int $begin, ?int $count)
    {
        $data = [
            'type'     => $type,
            'page_ids' => $page_ids,
            'begin'    => $begin,
            'count'    => $count,
        ];
        return $this->post('shakearound/page/search', $data);
    }

    /**
     * 删除页面
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Pages_management/Delete_page.html
     *
     * @param int $page_id 指定页面的id
     *
     * @return array
     * @throws Exception
     */
    public function deletePage(int $page_id)
    {
        return $this->post('shakearound/page/delete', ['page_id' => $page_id]);
    }

    /**
     * 上传图片素材
     *
     * @param string      $filename 文件路径
     * @param string|null $type     Icon：摇一摇页面展示的icon图；License：申请开通摇一摇周边功能时需上传的资质文件；若不传type，则默认type=icon
     *
     * @return array
     * @throws Exception
     */
    public function uploadMaterial(string $filename, ?string $type = 'icon')
    {
        return $this->httpUpload('shakearound/material/add', $filename, ['type' => $type]);
    }

    /**
     * 配置设备与页面的关联关系
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Relationship_between_pages_and_devices/Device_settings_and_page_associations.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function bindPage(array $data)
    {
        return $this->post('shakearound/device/bindpage', $data);
    }

    /**
     * 查询设备与页面的关联关系
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Relationship_between_pages_and_devices/Querying_Device_and_Page_Associations.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function queryPage(array $data)
    {
        return $this->post('shakearound/relation/search', $data);
    }

    /**
     * 以设备为维度的数据统计接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Analytics/Using_devices_as_a_dimension_for_the_data_statistics_interface.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function totalDevice(array $data)
    {
        return $this->post('shakearound/statistics/device', $data);
    }

    /**
     * 录入红包信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Shake_RedPack/Entering_Red_packet_Information.html
     *
     * @param string $lottery_id      string    红包抽奖id，来自addlotteryinfo返回的lottery_id
     * @param string $mchid           string    红包提供者的商户号，，需与预下单中的商户号mch_id一致
     * @param string $sponsor_appid   string    红包提供商户公众号的appid，需与预下单中的公众账号appid（wxappid）一致
     * @param array  $prize_info_list json数组    红包ticket列表，如果红包数较多，可以一次传入多个红包，批量调用该接口设置红包信息。每次请求传入的红包个数上限为100
     * @param string $ticket          string    预下单时返回的红包ticket，单个活动红包ticket数量上限为100000个，可添加多次。
     *
     * @return mixed
     * @throws Exception
     */
    public function setPrizeBucket(string $lottery_id, string $mchid, string $sponsor_appid, array $prize_info_list, string $ticket)
    {
        $data = [
            'lottery_id'      => $lottery_id,
            'mchid'           => $mchid,
            'sponsor_appid'   => $sponsor_appid,
            'prize_info_list' => $prize_info_list,
            'ticket'          => $ticket,
        ];
        return $this->post('shakearound/lottery/setprizebucket', $data);
    }

    /**
     * 批量查询设备统计数据接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Analytics/Batch_query_device_statistics_data_interface.html
     *
     * @param int $date       指定查询日期时间戳，单位为秒 例如：1438704000
     * @param int $page_index 指定查询的结果页序号；返回结果按摇周边人数降序排序，每50条记录为一页
     *
     * @return array
     * @throws Exception
     */
    public function totalDeviceList(int $date, int $page_index = 1)
    {
        return $this->post('shakearound/statistics/devicelist', ['date' => $date, 'page_index' => $page_index]);
    }

    /**
     * 以页面为维度的数据统计接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Analytics/Using_pages_as_a_dimension_for_the_data_statistics_interface.html
     *
     * @param int $page_id    指定页面的设备ID
     * @param int $begin_date 起始日期时间戳，最长时间跨度为30天，单位为秒
     * @param int $end_date   结束日期时间戳，最长时间跨度为30天，单位为秒
     *
     * @return array
     * @throws Exception
     */
    public function totalPage(int $page_id, int $begin_date, int $end_date)
    {
        return $this->post('shakearound/statistics/page', ['page_id' => $page_id, 'begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 查询指定时间商家账号下的每个页面进行摇周边操作的人数、次数，点击摇周边消息的人数、次数。
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Analytics/Batch_Query_API_for_Page_Statistics.html
     *
     * @param int $date       时间戳 1425139200
     * @param int $page_index 指定查询的结果页序号；返回结果按摇周边人数降序排序，每50条记录为一页
     *
     * @return mixed
     * @throws Exception
     */
    public function getPagelist(int $date, int $page_index = 1)
    {
        return $this->post('shakearound/statistics/pagelist', ['date' => $date, 'page_index' => $page_index]);
    }

    /**
     * 编辑分组信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Group_editing_information.html
     *
     * @param int    $group_id   分组唯一标识，全局唯一
     * @param string $group_name 分组名称，不超过100汉字或200个英文字母
     *
     * @return array
     * @throws Exception
     */
    public function updateGroup(int $group_id, string $group_name)
    {
        return $this->post('shakearound/device/group/update', ['group_id' => $group_id, 'group_name' => $group_name]);
    }

    /**
     * 删除分组
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Delete_grouping.html
     *
     * @param integer $group_id 分组唯一标识，全局唯一
     *
     * @return array
     * @throws Exception
     */
    public function deleteGroup(int $group_id)
    {
        return $this->post('shakearound/device/group/delete', ['group_id' => $group_id]);
    }

    /**
     * 查询分组列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Search_groups_list.html
     *
     * @param int $begin 分组列表的起始索引值
     * @param int $count 待查询的分组数量，不能超过1000个
     *
     * @return array
     * @throws Exception
     */
    public function getGroupList(int $begin = 0, int $count = 10)
    {
        return $this->post('shakearound/device/group/getlist', ['begin' => $begin, 'count' => $count]);
    }


    /**
     * 查询分组详情
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Search_grouping_details.html
     *
     * @param int $group_id 分组唯一标识，全局唯一
     * @param int $begin    分组里设备的起始索引值
     * @param int $count    待查询的分组里设备的数量，不能超过1000个
     *
     * @return array
     * @throws Exception
     */
    public function getGroupDetail(int $group_id, int $begin = 0, int $count = 100)
    {
        return $this->post('shakearound/device/group/getdetail', ['group_id' => $group_id, 'begin' => $begin, 'count' => $count]);
    }

    /**
     * 添加设备到分组
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Add_device_to_group.html
     *
     * @param array  $device_identifiers 设备id列表
     *                                   device_id    是    设备编号，若填了UUID、major、minor，即可不填设备编号，二者选其一
     *                                   UUID、major、minor    是    UUID、major、minor，三个信息需填写完成，若填了设备编号，即可不填此信息，二者选其一
     * @param string $group_id           是    分组唯一标识，全局唯一
     *
     * @return array
     * @throws Exception
     */
    public function addDeviceGroup(array $device_identifiers, string $group_id)
    {
        $data = [
            'group_id'           => $group_id,
            'device_identifiers' => $device_identifiers,
        ];

        return $this->post('shakearound/device/group/adddevice', $data);
    }

    /**
     * 添加设备分组
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/New_groups.html
     *
     * @param string $group_name 分组名称，不超过100汉字或200个英文字母
     *
     * @return mixed
     * @throws Exception
     */
    public function addGroup(string $group_name)
    {
        $data = [
            'group_name' => $group_name,
        ];

        return $this->post('shakearound/device/group/add', $data);
    }

    /**
     * 从分组中移除设备
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shake_Nearby/Active_from_Html5/Remove_device_from_group.html
     *
     * @param array  $device_identifiers 设备id列表
     *                                   device_id    是    设备编号，若填了UUID、major、minor，即可不填设备编号，二者选其一
     *                                   UUID、major、minor    是    UUID、major、minor，三个信息需填写完成，若填了设备编号，即可不填此信息，二者选其一
     * @param string $group_id           是    分组唯一标识，全局唯一
     *
     * @return array
     * @throws Exception
     */
    public function deleteDeviceGroup(array $device_identifiers, string $group_id)
    {
        $data = [
            'group_id'           => $group_id,
            'device_identifiers' => $device_identifiers,
        ];

        return $this->post('shakearound/device/group/deletedevice', $data);
    }
}