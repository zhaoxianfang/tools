<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 微信菜单管理
 * Class Menu
 *
 * @package WeChat
 */
class Menu extends WeChatBase
{

    /**
     * 自定义菜单查询接口
     *
     * @return array
     * @throws Exception
     */
    public function getMenu()
    {
        return $this->get("cgi-bin/menu/get");
    }

    /**
     * 自定义菜单删除接口
     *
     * @return array
     * @throws Exception
     */
    public function deleteMenu()
    {
        return $this->get("cgi-bin/menu/delete");
    }

    /**
     * 自定义菜单创建
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function createMenu(array $data)
    {
        return $this->post("cgi-bin/menu/create", $data);
    }

    /**
     * 创建个性化菜单
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addConditional(array $data)
    {
        return $this->post("cgi-bin/menu/addconditional", $data);
    }

    /**
     * 删除个性化菜单
     *
     * @param string $menuid
     *
     * @return array
     * @throws Exception
     */
    public function delConditional($menuid)
    {
        return $this->post("cgi-bin/menu/delconditional", ["menuid" => $menuid]);
    }

    /**
     * 测试个性化菜单匹配结果
     *
     * @param string $openid
     *
     * @return array
     * @throws Exception
     */
    public function tryConditional($openid)
    {
        return $this->post("cgi-bin/menu/trymatch", ["user_id" => $openid]);
    }
}