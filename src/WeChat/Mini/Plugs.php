<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信小程序插件管理
 * Class Plugs
 * @package WeMini
 */
class Plugs extends WeChatBase
{
    /**
     * 1.申请使用插件
     * @param string $plugin_appid 插件appid
     * @return array
     * @throws Exception
     */
    public function apply($plugin_appid)
    {
        $url = 'wxa/plugin';
        return $this->callPostApi($url, ['action' => 'apply', 'plugin_appid' => $plugin_appid], true);
    }

    /**
     * 2.查询已添加的插件
     * @return array
     * @throws Exception
     */
    public function getList()
    {
        $url = 'wxa/plugin';
        return $this->callPostApi($url, ['action' => 'list'], true);
    }

    /**
     * 3.删除已添加的插件
     * @param string $plugin_appid 插件appid
     * @return array
     * @throws Exception
     */
    public function unbind($plugin_appid)
    {
        $url = 'wxa/plugin';
        return $this->callPostApi($url, ['action' => 'unbind', 'plugin_appid' => $plugin_appid], true);
    }

    /**
     * 获取当前所有插件使用方
     * 修改插件使用申请的状态
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function devplugin($data)
    {
        $url = 'wxa/devplugin';
        return $this->post($url, $data);
    }

    /**
     * 4.获取当前所有插件使用方（供插件开发者调用）
     * @param integer $page 拉取第page页的数据
     * @param integer $num 表示每页num条记录
     * @return array
     * @throws Exception
     */
    public function devApplyList($page = 1, $num = 10)
    {
        $url = 'wxa/plugin';
        $data = ['action' => 'dev_apply_list', 'page' => $page, 'num' => $num];
        return $this->post($url, $data);
    }

    /**
     * 5.修改插件使用申请的状态（供插件开发者调用）
     * @param string $action dev_agree：同意申请；dev_refuse：拒绝申请；dev_delete：删除已拒绝的申请者
     * @return array
     * @throws Exception
     */
    public function devAgree($action = 'dev_agree')
    {
        $url = 'wxa/plugin';
        return $this->callPostApi($url, ['action' => $action], true);
    }
}