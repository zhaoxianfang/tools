<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信菜单管理
 */
class Menu extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 自定义菜单创建
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function create(array $data)
    {
        return $this->post('cgi-bin/menu/create', $data);
    }

    /**
     * 获取自定义菜单(API创建的菜单)
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html
     *
     * @return void
     *
     * @throws Exception
     */
    public function getMenuOnlyApi()
    {
        return $this->get('cgi-bin/menu/get');
    }

    /**
     * 自定义菜单查询接口(API创建或者微信后台创建的自定义菜单)
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Querying_Custom_Menus.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function getMenu()
    {
        return $this->get('cgi-bin/get_current_selfmenu_info');
    }

    /**
     * 自定义菜单删除接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Deleting_Custom-Defined_Menu.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function delete()
    {
        return $this->get('cgi-bin/menu/delete');
    }

    /**
     * 创建个性化菜单
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function addConditional(array $data)
    {
        return $this->post('cgi-bin/menu/addconditional', $data);
    }

    /**
     * 删除个性化菜单
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html#1
     *
     * @return array
     *
     * @throws Exception
     */
    public function delConditional(string $menuid)
    {
        return $this->post('cgi-bin/menu/delconditional', ['menuid' => $menuid]);
    }

    /**
     * 测试个性化菜单匹配结果
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html#2
     *
     * @param  string  $openid  可以是粉丝的OpenID，也可以是粉丝的微信号。
     * @return array
     *
     * @throws Exception
     */
    public function tryConditional($openid)
    {
        return $this->post('cgi-bin/menu/trymatch', ['user_id' => $openid]);
    }
}
