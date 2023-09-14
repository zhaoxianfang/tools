<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

class Wifi extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 获取 Wi-Fi 门店列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Stores_management/Get_a_Wi-Fi_store_list.html
     *
     * @param int|null $pageindex 分页下标，默认从1开始
     * @param int|null $pagesize  每页的个数，默认10个，最大20个
     *
     * @return array
     * @throws Exception
     */
    public function getShopList(?int $pageindex = 1, ?int $pagesize = 2)
    {
        return $this->post('bizwifi/shop/list', ['pageindex' => $pageindex, 'pagesize' => $pagesize]);
    }

    /**
     * 修改门店网络信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Stores_management/Modify_the_store_network_information.html
     *
     * @param int         $shop_id  门店ID
     * @param string      $old_ssid 旧的无线网络设备的ssid
     * @param string      $ssid     新的无线网络设备的ssid
     * @param string|null $password 无线网络设备的密码(可选)
     *
     * @return array
     * @throws Exception
     */
    public function upShopWifi(int $shop_id, string $old_ssid, string $ssid, ?string $password = null)
    {
        $data = ['shop_id' => $shop_id, 'old_ssid' => $old_ssid, 'ssid' => $ssid];
        is_null($password) || $data['password'] = $password;
        return $this->post('bizwifi/shop/update', $data);
    }

    /**
     * 查询某一门店的详细Wi-Fi信息，包括门店内的设备类型、ssid、密码、设备数量、商家主页URL、顶部常驻入口文案
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Stores_management/Query_store_Wi-Fi_information.html
     *
     * @param int $shop_id 门店ID
     *
     * @return mixed
     * @throws Exception
     */
    public function getShopWifiInfo(int $shop_id)
    {
        return $this->post('bizwifi/shop/get', ['shop_id' => $shop_id]);
    }

    /**
     * 清空门店网络及设备
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Stores_management/Empty_the_store_network_and_devices.html
     *
     * @param int      $shop_id 门店ID
     * @param int|null $ssid    无线网络设备的ssid。若不填写ssid，默认为清空门店下所有设备；填写ssid则为清空该ssid下的所有设备
     *
     * @return array
     * @throws Exception
     */
    public function clearShopWifi(int $shop_id, ?int $ssid = null)
    {
        return $this->post('bizwifi/shop/clean', ['shop_id' => $shop_id, 'ssid' => $ssid]);
    }

    /**
     * 第三方平台获取开插件wifi_token
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Launch_WeChat_Wi-Fi_Connection_Plug-in.html#3
     *
     * @param string $callback_url
     *
     * @return mixed
     * @throws Exception
     */
    public function openPluginToken(string $callback_url)
    {
        return $this->post('bizwifi/openplugin/token', ['callback_url' => $callback_url]);
    }

    /**
     * 引导用户进入开通插件页面
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Launch_WeChat_Wi-Fi_Connection_Plug-in.html#5
     *
     * @param string $token 第三方平台需提供已获取的wifi_token。
     *
     * @return string
     */
    public function gotoOpenPlugin(string $token)
    {
        return 'https://wifi.weixin.qq.com/biz/mp/thirdProviderPlugin.xhtml?token=' . $token;
    }


    /**
     * 添加密码型设备
     *
     * @param int    $shop_id  门店ID
     * @param string $ssid     无线网络设备的ssid
     * @param string $password 无线网络设备的密码。 8-24个字符；不能包含中文字符；ssid和密码必须有一个以大写字母“WX”开头
     *
     * @return array
     * @throws Exception
     */
    public function addShopWifi(int $shop_id, string $ssid, string $password)
    {
        $data = ['shop_id' => $shop_id, 'ssid' => $ssid, 'password' => $password];
        return $this->post('bizwifi/device/add', $data);
    }

    /**
     * 添加portal型设备
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Devices_Management/Add_portal_type_device.html
     *
     * @param int    $shop_id 门店ID
     * @param string $ssid    无线网络设备的ssid
     * @param bool   $reset   重置secretkey，false-不重置，true-重置，默认为false
     *
     * @return array
     * @throws Exception
     */
    public function addShopPortal(int $shop_id, string $ssid, ?bool $reset = false)
    {
        $data = ['shop_id' => $shop_id, 'ssid' => $ssid, 'reset' => $reset];

        return $this->post('bizwifi/apportal/register', $data);
    }

    /**
     * 查询设备
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Devices_Management/Query_devices.html
     *
     * @param null|int $shop_id   根据门店id查询
     * @param null|int $pageindex 分页下标，默认从1开始
     * @param null|int $pagesize  每页的个数，默认10个，最大20个
     *
     * @return array
     * @throws Exception
     */
    public function queryShopWifi(?int $shop_id = null, ?int $pageindex = null, ?int $pagesize = null)
    {
        $data = [];
        is_null($pagesize) || $data['pagesize'] = $pagesize;
        is_null($pageindex) || $data['pageindex'] = $pageindex;
        is_null($shop_id) || $data['shop_id'] = $shop_id;
        return $this->post('bizwifi/device/list', $data);
    }

    /**
     * 删除设备
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Devices_Management/Delete_devices.html
     *
     * @param string $bssid 需要删除的无线网络设备无线mac地址，格式冒号分隔，字符长度17个，并且字母小写，例如：00:1f:7a:ad:5c:a8
     *
     * @return array
     * @throws Exception
     */
    public function delShopWifi(string $bssid)
    {
        return $this->post('bizwifi/device/delete', ['bssid' => $bssid]);
    }

    /**
     * 获取物料二维码
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Connecting/Get_materials_QR_Code.html
     *
     * @param int    $shop_id 门店ID
     * @param string $ssid    已添加到门店下的无线网络名称
     * @param int    $img_id  物料样式编号：0-纯二维码，可用于自由设计宣传材料；1-二维码物料，155mm×215mm(宽×高)，可直接张贴
     *
     * @return array
     * @throws Exception
     */
    public function getQrc(int $shop_id, string $ssid, int $img_id = 1)
    {

        return $this->post('bizwifi/qrcode/get', ['shop_id' => $shop_id, 'ssid' => $ssid, 'img_id' => $img_id]);
    }

    /**
     * 设置商家主页
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Home_Page_management/Set_up_a_merchant_home_page.html
     *
     * @param string      $shop_id     门店ID
     * @param string      $template_id 模板ID，0-默认模板，1-自定义url
     * @param null|string $url         自定义链接，当template_id为1时必填
     *
     * @return array
     * @throws Exception
     */
    public function setHomePage(string $shop_id, string $template_id, ?string $url = null)
    {
        $data = ['shop_id' => $shop_id, 'template_id' => $template_id];
        is_null($url) && $data['struct'] = ['url' => $url];

        return $this->post('bizwifi/homepage/set', $data);
    }

    /**
     * 查询商家主页
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Home_Page_management/Query_merchants_home_page.html
     *
     * @param int $shop_id 查询的门店id
     *
     * @return array
     * @throws Exception
     */
    public function getHomePage(int $shop_id)
    {
        return $this->post('bizwifi/homepage/get', ['shop_id' => $shop_id]);
    }

    /**
     * 设置微信首页欢迎语
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Home_Page_management/Set_up_WeChat_home_page_greeting_messages.html
     *
     * @param int $shop_id  门店ID
     * @param int $bar_type 微信首页欢迎语的文本内容：0--欢迎光临+公众号名称；1--欢迎光临+门店名称；2--已连接+公众号名称+WiFi；3--已连接+门店名称+Wi-Fi。
     *
     * @return array
     * @throws Exception
     */
    public function setBar(int $shop_id, int $bar_type = 1)
    {
        return $this->post('bizwifi/bar/set', ['shop_id' => $shop_id, 'bar_type' => $bar_type]);
    }

    /**
     * 设置连网完成页
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Home_Page_management/Set_up_the_Wi-Fi_Connection_Completion_page.html
     *
     * @param int    $shop_id        门店ID
     * @param string $finishpage_url 连网完成页URL
     *
     * @return array
     * @throws Exception
     */
    public function setFinishPage(int $shop_id, string $finishpage_url)
    {
        return $this->post('bizwifi/finishpage/set', ['shop_id' => $shop_id, 'finishpage_url' => $finishpage_url]);
    }

    /**
     * Wi-Fi 数据统计
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Wi-Fi_data_statistics.html
     *
     * @param string   $begin_date 起始日期时间，格式yyyy-mm-dd，最长时间跨度为30天
     * @param string   $end_date   结束日期时间戳，格式yyyy-mm-dd，最长时间跨度为30天
     * @param int|null $shop_id    按门店ID搜索，-1为总统计
     *
     * @return array
     * @throws Exception
     */
    public function staticList(string $begin_date, string $end_date, ?int $shop_id = -1)
    {
        return $this->post('bizwifi/statistics/list', ['shop_id' => $shop_id, 'begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 设置门店卡券投放信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Cards/Set_store_card_and_coupon_delivery_information.html
     *
     * @param int    $shop_id       门店ID，可设置为0，表示所有门店
     * @param string $card_id       卡券ID
     * @param string $card_describe 卡券描述，不能超过18个字符
     * @param int    $start_time    卡券投放开始时间（单位是秒）
     * @param int    $end_time      卡券投放结束时间（单位是秒） 注：不能超过卡券的有效期时间
     *
     * @return array
     * @throws Exception
     */
    public function setCouponput(int $shop_id, string $card_id, string $card_describe, int $start_time, int $end_time)
    {
        $data = ['shop_id' => $shop_id, 'card_id' => $card_id, 'card_describe' => $card_describe, 'start_time' => $start_time, 'end_time' => $end_time];

        return $this->post('bizwifi/couponput/set', $data);
    }

    /**
     * 查询门店卡券投放信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/WiFi_via_WeChat/Cards/Query_the_store_card_and_coupon_delivery_information.html
     *
     * @param int $shop_id 门店ID，可设置为0，表示所有门店
     *
     * @return array
     * @throws Exception
     */
    public function getCouponput(int $shop_id)
    {
        return $this->post('bizwifi/couponput/get', ['shop_id' => $shop_id]);
    }
}