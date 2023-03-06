<?php

namespace zxf\Facade\Wechat;

use zxf\Facade\FacadeBase;
use zxf\Facade\FacadeInterface;

/**
 * 微信公众号菜单
 *
 * @method static mixed getMenu()                   获取微信自定义菜单
 * @method static mixed deleteMenu()                删除微信自定义菜单
 * @method static mixed createMenu(array $data)     创建微信自定义菜单
 * @method static mixed addConditional(array $data) 创建个性化菜单管理
 * @method static mixed delConditional($menuid)     删除指定微信个性化菜单
 * @method static mixed tryConditional($openid)     测试微信个性化菜单
 */
class Menu extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\WeChat\Offiaccount\Menu::class;
    }
}