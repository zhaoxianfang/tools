<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信小程序插件管理
 */
class Plugs extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 1.申请使用插件
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePlugin.html#%E8%AF%B7%E6%B1%82%E6%95%B0%E6%8D%AE%E7%A4%BA%E4%BE%8B
     *
     * @param  string  $plugin_appid  插件appid
     * @param  string  $reason  当action是"apply"时必填，申请原因
     * @return array
     *
     * @throws Exception
     */
    public function apply($plugin_appid, string $reason)
    {
        return $this->post('wxa/plugin', ['action' => 'apply', 'plugin_appid' => $plugin_appid, 'reason' => $reason]);
    }

    /**
     * 2.更新插件
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePlugin.html#%E8%AF%B7%E6%B1%82%E6%95%B0%E6%8D%AE%E7%A4%BA%E4%BE%8B
     *
     * @param  string  $plugin_appid  插件appid 插件的 appid
     * @param  string  $user_version  当action是"update"时使用。升级至版本号，要求此插件版本支持快速更新
     * @return array
     *
     * @throws Exception
     */
    public function update(string $plugin_appid, string $user_version)
    {
        return $this->post('wxa/plugin', ['action' => 'update', 'plugin_appid' => $plugin_appid]);
    }

    /**
     * 3.获取插件列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePlugin.html#%E8%AF%B7%E6%B1%82%E6%95%B0%E6%8D%AE%E7%A4%BA%E4%BE%8B
     *
     * @return array
     *
     * @throws Exception
     */
    public function getList()
    {
        return $this->post('wxa/plugin', ['action' => 'list']);
    }

    /**
     * 4.删除已添加的插件
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePlugin.html#%E8%AF%B7%E6%B1%82%E6%95%B0%E6%8D%AE%E7%A4%BA%E4%BE%8B
     *
     * @param  string  $plugin_appid  插件appid
     * @return array
     *
     * @throws Exception
     */
    public function unbind($plugin_appid)
    {
        return $this->post('wxa/plugin', ['action' => 'unbind', 'plugin_appid' => $plugin_appid]);
    }

    /**
     * 插件申请管理
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePluginApplication.html
     *
     * @param  string  $action  string    是
     *                          dev_agree表示同意申请、dev_refuse表示拒绝申请、dev_delete表示删除已拒绝的申请者、dev_apply_list表示获取当前所有插件使用方信息
     * @param  string|null  $appid  string    否    action为"dev_agree"时填写，使用者的 appid，同意申请时填写。
     * @param  int|null  $page  number    否    action为"dev_apply_list"时填写，要拉取第几页的数据
     * @param  int|null  $num  number    否    action为"dev_apply_list"时填写，每页的记录数
     * @param  string|null  $reason  string    否    action为"dev_refuse"时填写，拒绝理由。
     * @return array
     *
     * @throws Exception
     */
    public function devplugin(string $action, ?string $appid = '', ?int $page = 1, ?int $num = 10, ?string $reason = '')
    {
        $data = [
            'action' => $action,
            'appid' => $appid,
            'page' => $page,
            'num' => $num,
            'reason' => $reason,
        ];

        return $this->post('wxa/devplugin', $data);
    }

    /**
     * 4.获取当前所有插件使用方（供插件开发者调用）
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePluginApplication.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param  int  $page  拉取第page页的数据
     * @param  int  $num  表示每页num条记录
     * @return array
     *
     * @throws Exception
     */
    public function devApplyList($page = 1, $num = 10)
    {
        $data = ['action' => 'dev_apply_list', 'page' => $page, 'num' => $num];

        return $this->post('wxa/plugin', $data);
    }

    /**
     * 5.修改插件使用申请的状态（供插件开发者调用）
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/plugin-management/managePluginApplication.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param  string  $action  dev_agree：同意申请；dev_refuse：拒绝申请；dev_delete：删除已拒绝的申请者
     * @param  string|null  $appid  action为"dev_agree"时填写，使用者的 appid，同意申请时填写
     * @return array
     *
     * @throws Exception
     */
    public function devAgree(string $action = 'dev_agree', ?string $appid = '')
    {
        return $this->post('wxa/devplugin', ['action' => $action, 'appid' => $appid]);
    }
}
