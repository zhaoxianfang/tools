<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 门店 WIFI 管理
 * Class Wifi
 *
 * @package WeChat
 */
class Wifi extends WeChatBase
{

    /**
     * 获取 Wi-Fi 门店列表
     *
     * @param integer $pageindex 分页下标，默认从1开始
     * @param integer $pagesize  每页的个数，默认10个，最大20个
     *
     * @return array
     * @throws Exception
     */
    public function getShopList($pageindex = 1, $pagesize = 2)
    {
        return $this->post("bizwifi/shop/list", ["pageindex" => $pageindex, "pagesize" => $pagesize]);
    }

    /**
     * 查询门店Wi-Fi信息
     *
     * @param integer $shop_id 门店ID
     *
     * @return array
     * @throws Exception
     */
    public function getShopWifi($shop_id)
    {
        return $this->post("bizwifi/shop/list", ["shop_id" => $shop_id]);
    }

    /**
     * 修改门店网络信息
     *
     * @param integer $shop_id  门店ID
     * @param string  $old_ssid 旧的无线网络设备的ssid
     * @param string  $ssid     新的无线网络设备的ssid
     * @param string  $password 无线网络设备的密码(可选)
     *
     * @return array
     * @throws Exception
     */
    public function upShopWifi($shop_id, $old_ssid, $ssid, $password = null)
    {
        $data = ["shop_id" => $shop_id, "old_ssid" => $old_ssid, "ssid" => $ssid];
        is_null($password) || $data["password"] = $password;

        return $this->post("bizwifi/shop/update", $data);
    }

    /**
     * 清空门店网络及设备
     *
     * @param integer $shop_id
     *
     * @return array
     * @throws Exception
     */
    public function clearShopWifi($shop_id)
    {
        return $this->post("bizwifi/shop/clean", ["shop_id" => $shop_id]);
    }

    /**
     * 添加密码型设备
     *
     * @param integer     $shop_id  门店ID
     * @param string      $ssid     无线网络设备的ssid
     * @param null|string $password 无线网络设备的密码
     *
     * @return array
     * @throws Exception
     */
    public function addShopWifi($shop_id, $ssid, $password = null)
    {
        $data = ["shop_id" => $shop_id, "ssid" => $ssid, "password" => $password];

        return $this->post("bizwifi/device/add", $data);
    }

    /**
     * 添加portal型设备
     *
     * @param integer $shop_id 门店ID
     * @param string  $ssid    无线网络设备的ssid
     * @param bool    $reset   重置secretkey，false-不重置，true-重置，默认为false
     *
     * @return array
     * @throws Exception
     */
    public function addShopPortal($shop_id, $ssid, $reset = false)
    {
        $data = ["shop_id" => $shop_id, "ssid" => $ssid, "reset" => $reset];

        return $this->post("bizwifi/apportal/register", $data);
    }

    /**
     * 查询设备
     *
     * @param null|integer $shop_id   根据门店id查询
     * @param null|integer $pageindex 分页下标，默认从1开始
     * @param null|integer $pagesize  每页的个数，默认10个，最大20个
     *
     * @return array
     * @throws Exception
     */
    public function queryShopWifi($shop_id = null, $pageindex = null, $pagesize = null)
    {
        $data = [];
        is_null($pagesize) || $data["pagesize"] = $pagesize;
        is_null($pageindex) || $data["pageindex"] = $pageindex;
        is_null($shop_id) || $data["shop_id"] = $shop_id;

        return $this->post("bizwifi/device/list", $data);
    }

    /**
     * 删除设备
     *
     * @param string $bssid 需要删除的无线网络设备无线mac地址，格式冒号分隔，字符长度17个，并且字母小写，例如：00:1f:7a:ad:5c:a8
     *
     * @return array
     * @throws Exception
     */
    public function delShopWifi($bssid)
    {
        return $this->post("bizwifi/device/delete", ["bssid" => $bssid]);
    }

    /**
     * 获取物料二维码
     *
     * @param integer $shop_id 门店ID
     * @param string  $ssid    已添加到门店下的无线网络名称
     * @param integer $img_id  物料样式编号：0-纯二维码，可用于自由设计宣传材料；1-二维码物料，155mm×215mm(宽×高)，可直接张贴
     *
     * @return array
     * @throws Exception
     */
    public function getQrc($shop_id, $ssid, $img_id = 1)
    {
        return $this->post("bizwifi/qrcode/get", ["shop_id" => $shop_id, "ssid" => $ssid, "img_id" => $img_id]);
    }

    /**
     * 设置商家主页
     *
     * @param integer     $shop_id     门店ID
     * @param integer     $template_id 模板ID，0-默认模板，1-自定义url
     * @param null|string $url         自定义链接，当template_id为1时必填
     *
     * @return array
     * @throws Exception
     */
    public function setHomePage($shop_id, $template_id, $url = null)
    {
        $data = ["shop_id" => $shop_id, "template_id" => $template_id];
        is_null($url) && $data["struct"] = ["url" => $url];

        return $this->post("bizwifi/homepage/set", $data);
    }

    /**
     * 查询商家主页
     *
     * @param integer $shop_id 查询的门店id
     *
     * @return array
     * @throws Exception
     */
    public function getHomePage($shop_id)
    {
        return $this->post("bizwifi/homepage/get", ["shop_id" => $shop_id]);
    }

    /**
     * 设置微信首页欢迎语
     *
     * @param integer $shop_id  门店ID
     * @param integer $bar_type 微信首页欢迎语的文本内容：0--欢迎光临+公众号名称；1--欢迎光临+门店名称；2--已连接+公众号名称+WiFi；3--已连接+门店名称+Wi-Fi。
     *
     * @return array
     * @throws Exception
     */
    public function setBar($shop_id, $bar_type = 1)
    {
        return $this->post("bizwifi/bar/set", ["shop_id" => $shop_id, "bar_type" => $bar_type]);
    }

    /**
     * 设置连网完成页
     *
     * @param integer $shop_id        门店ID
     * @param string  $finishpage_url 连网完成页URL
     *
     * @return array
     * @throws Exception
     */
    public function setFinishPage($shop_id, $finishpage_url)
    {
        return $this->post("bizwifi/finishpage/set", ["shop_id" => $shop_id, "finishpage_url" => $finishpage_url]);
    }

    /**
     * Wi-Fi 数据统计
     *
     * @param string  $begin_date 起始日期时间，格式yyyy-mm-dd，最长时间跨度为30天
     * @param string  $end_date   结束日期时间戳，格式yyyy-mm-dd，最长时间跨度为30天
     * @param integer $shop_id    按门店ID搜索，-1为总统计
     *
     * @return array
     * @throws Exception
     */
    public function staticList($begin_date, $end_date, $shop_id = -1)
    {
        return $this->post("bizwifi/statistics/list", ["shop_id" => $shop_id, "begin_date" => $begin_date, "end_date" => $end_date]);
    }

    /**
     * 设置门店卡券投放信息
     *
     * @param integer $shop_id       门店ID，可设置为0，表示所有门店
     * @param integer $card_id       卡券ID
     * @param string  $card_describe 卡券描述，不能超过18个字符
     * @param string  $start_time    卡券投放开始时间（单位是秒）
     * @param string  $end_time      卡券投放结束时间（单位是秒） 注：不能超过卡券的有效期时间
     *
     * @return array
     * @throws Exception
     */
    public function setCouponput($shop_id, $card_id, $card_describe, $start_time, $end_time)
    {
        $data = ["shop_id" => $shop_id, "card_id" => $card_id, "card_describe" => $card_describe, "start_time" => $start_time, "end_time" => $end_time];

        return $this->post("bizwifi/couponput/set", $data);
    }

    /**
     * 查询门店卡券投放信息
     *
     * @param integer $shop_id 门店ID，可设置为0，表示所有门店
     *
     * @return array
     * @throws Exception
     */
    public function getCouponput($shop_id)
    {
        return $this->post("bizwifi/couponput/get", ["shop_id" => $shop_id]);
    }

}