<?php

namespace zxf\extend;

/**
 * 自动生成后台菜单或Tree树形结构
 *
 * 列表应该包含的字段：id(主键)、pid(父级id)、title(展示字段)、name(调整地址)、weigh(权重)、icon(字体小图标)
 */
// 推荐表结构
//CREATE TABLE `你的表名` (
//  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
//  `pid` int NOT NULL DEFAULT '0' COMMENT '父级id',
//  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '路由地址 例如： admin/test 或者 admin.test',
//  `identify` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '菜单/按钮 权限唯一标识，例如：edit_system_config\n',
//  `title` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '菜单名称 例如：控制面板',
//  `ismenu` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '是否为菜单：1菜单，0按钮',
//  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
//  `icon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '小图标',
//  `badge_text` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '徽标',
//  `badge_text_style` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'label-info' COMMENT '徽标样式',
//  `create_by` bigint unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
//  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
//  `created_at` timestamp NULL DEFAULT NULL,
//  `updated_at` timestamp NULL DEFAULT NULL,
//  `status` tinyint NOT NULL DEFAULT '1' COMMENT '应用状态；1正常，0停用',
//  PRIMARY KEY (`id`) USING BTREE,
//  UNIQUE KEY `admin_menus_name_unique` (`name`) USING BTREE,
//  KEY `admin_menus_pid_index` (`pid`),
//  KEY `admin_menus_type_index` (`ismenu`),
//  KEY `admin_menus_create_by_index` (`create_by`),
//  KEY `admin_menus_status_index` (`status`)
//) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台菜单';

// demo
// Menu::instance()->init($ruleList)->setActiveMenu($urlLink)->createMenu(0);
// Menu::instance()->init($munuList)->setActiveMenu($urlLink)->setMenuType('inspinia')->createMenu(0);
// 顶部导航
// Menu::instance()->init($classifyList)->setTitle('name')->setUrlPrefix('/classify')->setActiveMenu($urlLink)->createMenu(0, 'home');
// 面包屑导航
// Menu::instance()->init($menuList, 'id', 'pid', 0, false)->getBreadCrumb('/admin/index/index', $raturnhtml = true, false);
// 菜单转换为视图
// Menu::instance()->init($arrList)->setWeigh()->getTree();

//目录类型 adminlte|layuiadmin|nazox|inspinia

// 目录类型对应的菜单的激活样式
// adminlte : $hasChild ? 'menu-open active' : 'active';
// layuiadmin : 'layui-this';
// nazox : 'mm-active';
// inspinia : 'active';

class Menu
{
    protected $arr           = [];
    protected $pk            = 'id';               // 主键
    protected $pid           = 'pid';              // 父级id
    protected $childlist     = 'childlist';        // 子级菜单键名
    protected $weigh         = 'weigh';            // 权重
    protected $title         = 'title';            // Tree 展示的作用字段
    protected $href          = 'name';             // Tree 中路由跳转的地址名称字段，一般为路由名称，也可以是url地址
    protected $icon          = '';                 // 左侧字体小图标
    protected $badge         = 'badge_text';       //badge 图标
    protected $badgeStyle    = 'badge_text_style'; //badge 图标 样式
    protected $showchildicon = false;              //子级菜单显示icon小图标
    protected $showNavIcon   = false;              //前台nav 一级导航是否显示icon小图标
    protected $menuType      = 'inspinia';            //目录类型 adminlte|layuiadmin|nazox|inspinia

    protected static $instance;
    //默认配置
    protected $config  = [];
    public    $options = [];
    //是否返回 $this
    protected $returnClass = false;
    //触发的菜单
    protected $activeMenu = '';
    //url地址前缀
    protected $urlPrefix = ''; //  /classify

    protected $domain = ''; // 域名

    protected $activeMenuIds   = []; // 查询出该被激活的菜单的所有父级菜单id
    protected $activeMenuItems = []; // 查询出该被激活的菜单的所有父级菜单列表

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     *
     * @var array
     */
    protected $iconStyle = array(' │', ' ├', ' └');

    public function __construct($options = [])
    {
        $this->options = !empty($options) ? array_merge($this->config, $options) : $this->config;
    }

    // 初始化参数，防止 调用 Menu 的过程中 有的 参数被篡改 导致 之后的调用参数发生错乱
    private function initConfigParam()
    {
        $this->arr           = [];
        $this->pk            = 'id';
        $this->pid           = 'pid';
        $this->childlist     = 'childlist';
        $this->weigh         = 'weigh';            //权重
        $this->title         = 'title';            //Tree 展示的作用字段
        $this->badge         = 'badge_text';       //badge 图标
        $this->badgeStyle    = 'badge_text_style'; //badge 图标 样式
        $this->showchildicon = false;              //子级菜单显示icon小图标
        $this->showNavIcon   = false;              //前台nav 一级导航是否显示icon小图标
        $this->menuType      = 'nazox';            //目录类型 adminlte|layuiadmin|nazox|inspinia

        //默认配置
        $this->config  = [];
        $this->options = [];
        //是否返回 $this
        $this->returnClass = false;
        //触发的菜单
        $this->activeMenu = '';
        //url地址前缀
        $this->urlPrefix = '';

        $this->domain = ''; // 域名
    }

    /**
     * 初始化
     *
     * @access public
     *
     * @param array $options 参数
     *
     * @return self
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 初始化方法
     *
     * @param array 2维数组，例如：
     *      array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     *      )
     */
    public function init($arr = [], $pk = 'id', $pid = 'pid', $rootId = 0, $initTree = true, $childlist = 'childlist')
    {
        $this->initConfigParam();

        $this->arr = $arr;
        $pk && $this->pk = $pk;
        $pid && $this->pid = $pid;
        $childlist && $this->childlist = $childlist;

        $this->setReturn(true);

        if ($initTree) {
            // 生成树
            // $this->arr = $this->arrayToTree();
            $this->arrayToTreeTwo();
        }

        return $this;
    }

    /**
     * 设置域名
     *
     * @param string $flag [description]
     */
    public function setDomain($domain = ''): self
    {
        $this->domain = $domain; // 域名
        return $this;
    }

    /**
     * 是否返回 $this 配合 getTreeTwo 用
     *
     * @param string $flag [description]
     */
    public function setReturn($flag = false): self
    {
        $this->returnClass = $flag ? true : false;
        return $this;
    }

    /**
     * 设置后台菜单是否包含icon 小图标
     *
     * @param boolean $flag [description]
     */
    public function setAdminMenuIcon($flag = false): self
    {
        $this->showchildicon = $flag ? true : false;
        return $this;
    }

    /**
     * 设置权重名
     *
     * @param boolean $str [description]
     */
    public function setWeigh($str = ''): self
    {
        $this->weigh = $str;
        return $this;
    }

    /**
     * 设置tree 展示的标题字段
     *
     * @param string $name [description]
     */
    public function setTitle($str = 'title')
    {
        $this->title = $str;
        return $this;
    }

    /**
     * 设置 Tree 中用来跳转的字段名，例如：href 或者 url,默认为 name
     *
     * @param string $str
     *
     * @return $this
     */
    public function setHref($str = 'name')
    {
        $this->href = $str;
        return $this;
    }

    public function getArr()
    {
        return $this->arr;
    }

    public function arrayToTree($list = [], $id = 'id', $pid = 'pid', $rootId = 0)
    {
        $list = !empty($list) ? $list : $this->arr;
        $data = [];
        foreach ($list as $row) {
            $data[$row[$id]]                               = $row;
            $data[$row[$pid]][$this->childlist][$row[$id]] = &$data[$row[$id]];
        }
        return isset($data[$rootId][$this->childlist]) ? $data[$rootId][$this->childlist] : [];
    }

    /**
     * 获取数 TREE
     *
     * @param    [type]       $arrData    [待处理的数组]
     * @param    [type]       $pid        [父id]
     * @param    [type]       $pk         [自增主键id]
     */
    public function arrayToTreeTwo()
    {
        $arrData = $this->arr;
        if (!$arrData) {
            return array();
        }
        $tree     = array();
        $usePkArr = []; ////记录被处理过的pid
        //第一步，将分类id作为数组key,并创建children单元
        foreach ($arrData as $arr) {
            $arr                                     = obj2Arr($arr);
            $tree[$arr[$this->pk]]                   = $arr;
            $tree[$arr[$this->pk]][$this->childlist] = array();

            $usePkArr[] = $arr[$this->pk];
        }
        //第二步，利用引用，将每个分类添加到父类children数组中，这样一次遍历即可形成树形结构。
        foreach ($tree as $key => $item) {
            if ($item[$this->pid] != 0) {

                $tree[$item[$this->pid]][$this->childlist][] = &$tree[$key]; //注意：此处必须传引用否则结果不对
                if ($tree[$key][$this->childlist] == null) {
                    unset($tree[$key][$this->childlist]); //如果children为空，则删除该children元素（可选）
                }
            }
        }
        // $tempArr = $tree; //临时存储 数据，如果 该树 没有根节点，那么就返回 没有根的数据，就不删除了
        //第三步，删除无用的非根节点数据
        foreach ($tree as $key => $t) {
            if (!isset($t[$this->pid])) {
                unset($tree[$key]);
                continue;
            }

            if ($t[$this->pid] != 0) {
                //2019-01-18 更新
                if (in_array($t[$this->pid], $usePkArr)) {
                    unset($tree[$key]);
                }
            }
        }
        if ($this->returnClass) {
            $this->arr = $tree;
            return $this;
        }
        return $tree;
    }

    /**
     * 生成排序后的菜单 每个子菜单紧跟在父菜单后面 权重值大的在前面
     *
     * @return array|mixed [type]       [description]
     */
    public function getTree()
    {
        if ($this->weigh && $this->arr) {
            $this->arr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $arrList = $this->reduce($this->arr, 0);

        foreach ($arrList as $key => &$value) {
            if (isset($value[$this->childlist])) {
                unset($value[$this->childlist]);
            }
        }
        return $arrList;
    }

    /**
     * 数组归纳 多维转二维
     *
     * @param array $arrData [description]
     * @param int   $lv      [级别 跟级为0]
     *
     * @return array|mixed [type]                [description]
     */
    private function reduce(array $arrData = [], $lv = 0)
    {
        $listArr = array();
        if (!$arrData) {
            return array();
        }
        $countArr = count($arrData) - 1;
        foreach ($arrData as $key => $value) {

            if ($lv > 0) {
                if ($key == $countArr) {
                    $value[$this->title] = str_repeat($this->iconStyle['0'], $lv - 1) . $this->iconStyle['2'] . $value[$this->title];
                } else {
                    $value[$this->title] = str_repeat($this->iconStyle['0'], $lv - 1) . $this->iconStyle['1'] . $value[$this->title];
                }
            }
            $listArr[] = $value;
            if (isset($value[$this->childlist]) && $value[$this->childlist]) {
                if ($this->weigh) {
                    $value[$this->childlist] = $this->my_sort($value[$this->childlist], $this->weigh, SORT_DESC, SORT_NUMERIC);
                }

                $lv++;
                $childArr = $this->reduce($value[$this->childlist], $lv);

                $listArr = !empty($childArr) ? array_merge($listArr, $childArr) : $listArr;
                $lv--;

            }
        }
        return $listArr;

    }

    /**
     * 自定义 数组排序
     *
     * @param array  $arrays     [被排序数组]
     * @param string $sort_key   [被排序字段]
     * @param string $sort_order [排序方式]
     * @param string $sort_type  [排序类型]
     *
     * @return   array|bool                   [description]
     */
    private function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        if (empty($key_arrays)) {
            return $arrays;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    public function setActiveMenu($activeMenu = '')
    {
        $this->activeMenu = $activeMenu ? strtolower(str_replace(".", "/", $activeMenu)) : '';
        return $this;
    }

    public function setUrlPrefix($prefixStr = '')
    {
        $this->urlPrefix = $prefixStr;
        return $this;
    }

    /**
     * 设置目录类型
     *
     * @param string $menuType          [默认adminlte目录]
     *                                  仅支持 adminlte|layuiadmin|nazox|inspinia
     */
    public function setMenuType($menuType = 'nazox')
    {
        $this->menuType = $menuType;
        return $this;
    }

    /**
     * [createMenu 创建目录] [默认adminlte风格目录--目前仅支持 adminlte|layuiadmin|nazox ]
     *
     * @param int    $pk    [顶级pid 一般为0]
     * @param string $scope [作用域，admin(后台)、home(前台)]
     *
     * @return   string              [description]
     */
    public function createMenu($pk = 0, $scope = 'admin'): string
    {
        // 获取激活菜单ids 和菜单items
        list($this->activeMenuIds, $this->activeMenuItems) = $this->getActiveMenuIds();

        if ($scope == 'admin') {
            //后台菜单
            if ($this->menuType == 'adminlte') {
                return $this->adminMenu($pk);
            }
            if ($this->menuType == 'layuiadmin') {
                return $this->layuiAdminMenu($pk);
            }
            if ($this->menuType == 'nazox') {
                return $this->nazoxMenu($pk);
            }
            if ($this->menuType == 'inspinia') {
                return $this->inspiniaMenu($pk);
            }
            return '';
        } else {
            // 前端顶部导航
            if ($this->menuType == 'adminlte') {
                // adminlte 前台顶部nav导航
                return $this->adminLtehomeNavMenu($pk);
            }
            if ($this->menuType == 'nazox') {
                // nazox 顶部nav导航
                return $this->nazoxhomeNavMenu($pk);
            }
            return '';
        }
    }

    public function inspiniaMenu($pk = 0, $lv = 0, $menu = array())
    {

        $str = '';
        $arr = !empty($menu) ? $menu : $this->arr;
        if (empty($arr) || !is_array($arr)) {
            return '';
        }
        $lv++;

        foreach ($arr as $key => $item) {
            $hasArrow = (isset($item[$this->childlist]) && !empty($item[$this->childlist])) ? true : false;

            $currentHref = $hasArrow ? 'javascript:;' : url($this->urlPrefix . $item[$this->href]); // 当前url
            $currentIcon = !empty($item[$this->icon]) ? $item[$this->icon] : '';                                   // 当前url

            $activeClass = $this->checkActiveMenu($item); // 激活的菜单样式
            $isShowUl    = ($hasArrow && $activeClass) ? 'in' : ''; // in 是否展开当前子菜单ul

            $str .= '<li class="' . $activeClass . '">';
            $str .= '<a href="' . $currentHref . '">';
            $str .= '<i class="fa ' . $currentIcon . '"></i>';

            $str .= '<span class="nav-label">' . $item[$this->title] . '</span>';
            // 子菜单
            $str .= $hasArrow ? '<span class="fa arrow"></span>' : '';

            // && // 右侧图标
            $item['badge_text'] && (
            $str .= '<span class="float-right label ' . (!empty($item['badge_text_style']) ? $item['badge_text_style'] : 'label-info') . '">' . $item['badge_text'] . '</span>'
            );

            $str .= '</a>';
            // 子菜单
            $str .= $hasArrow ? '<ul class="nav ' . ($lv < 2 ? 'nav-second-level' : 'nav-third-level') . ' collapse ' . $isShowUl . '" aria-expanded="false">' : '';

            $str .= $hasArrow ? $this->inspiniaMenu($item[$this->pk], $lv, $item[$this->childlist]) : '';
            $str .= $hasArrow ? '</ul>' : '';
        }
        return $str;
    }

    public function nazoxMenu($pk = 0, $lv = 0, $menu = array())
    {

        $str = '';
        $arr = !empty($menu) ? $menu : $this->arr;
        if (empty($arr) || !is_array($arr)) {
            return '';
        }
        $lv == 0 && ($str = '<li class="menu-title">菜单</li>');
        $lv++;

        foreach ($arr as $item) {
            $hasArrow = (isset($item[$this->childlist]) && !empty($item[$this->childlist])) ? true : false;

            $currentHref = $hasArrow ? 'javascript:;' : url($this->urlPrefix . $item[$this->href]); // 当前url
            $currentIcon = !empty($item[$this->icon]) ? $item[$this->icon] : '';                              // 当前url

            $activeClass = $this->checkActiveMenu($item); // 激活的菜单样式
            $isShowUl    = ($hasArrow && $activeClass) ? 'mm-show' : ''; // mm-show 是否展开当前子菜单ul

            $str .= '<li class="' . $activeClass . '">';
            $str .= '<a href="' . $currentHref . '" class="' . ($hasArrow ? 'has-arrow ' : ' ') . $activeClass . ' waves-effect">';
            $str .= '<i class="fa ' . $currentIcon . '"></i>';
            // && // 右侧图标
            $item['badge_text'] && (
            $str .= '<span class="badge badge-pill ' . (!empty($item['badge_text_style']) ? $item['badge_text_style'] : 'badge-info') . ' float-right">' . $item['badge_text'] . '</span>'
            );
            $str .= '<span>' . $item[$this->title] . '</span>';
            $str .= '</a>';
            // 子菜单
            $str .= $hasArrow ? '<ul class="sub-menu mm-collapse ' . $isShowUl . '" aria-expanded="false">' : '';

            $str .= $hasArrow ? $this->nazoxMenu($item[$this->pk], $lv, $item[$this->childlist]) : '';
            $str .= $hasArrow ? '</ul>' : '';
        }
        return $str;
    }

    /**
     * [创建layuiAdminMenu后台目录]
     *
     * @param    [type]       $pk    [description]
     *
     * @return   [type]              [description]
     */
    private function layuiAdminMenu($pk)
    {
        $str = '';
        if ($pk < 1) {
            $tArr = ($this->weigh && $this->arr) ? $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC) : $this->arr;
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);
        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        foreach ($childs as $key => $menu) {
            if (!$menu) {
                return '';
            }
            $icon    = !empty($menu[$this->icon]) ? $menu[$this->icon] : '';
            $layHref = (isset($menu[$this->childlist]) && !empty($menu[$this->childlist])) ? '' : 'lay-href="' . url($menu[$this->href]) . '"';

            $activeClass = $layHref ? $this->checkActiveMenu($menu) : ''; // 激活的菜单样式

            $str .= '<li data-name="' . $menu[$this->href] . '" class="layui-nav-item ' . $activeClass . '"><a href="javascript:;" lay-tips="' . $menu['title'] . '" ' . $layHref . ' lay-direction="2"><i class="' . $icon . '"></i><cite>' . $menu['title'] . '</cite></a>' . $this->layuiChildList($menu) . '</li>';
        }
        return $str;
    }

    /**
     * [layuiAdmin 目录子列表]
     *
     * @param string $parentData [description]
     *
     * @return string [type]                   [description]
     */
    private function layuiChildList($parentData = '')
    {
        if (!isset($parentData[$this->childlist]) || empty($parentData[$this->childlist])) {
            return '';
        }
        $str = '';
        if ($this->weigh && $parentData[$this->childlist]) {
            $parentData[$this->childlist] = $this->my_sort($parentData[$this->childlist], $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        foreach ($parentData[$this->childlist] as $key => $page) {
            $layHref = (isset($page[$this->childlist]) && !empty($page[$this->childlist])) ? '' : 'lay-href="' . url($page[$this->href]) . '"';

            $activeClass = $layHref ? $this->checkActiveMenu($page) : ''; // 激活的菜单样式

            $str .= '<dl class="layui-nav-child"><dd data-name="' . $page[$this->href] . '" class=" ' . $activeClass . '"><a href="javascript:;" ' . $layHref . ' >' . $page['title'] . '</a>' . $this->layuiChildList($page) . '</dd></dl>';
        }
        return $str;
    }

    /**
     * 创建后台目录 [adminLte 目录]
     *
     * @param    [type]       $pk   [description]
     * @param array $tArr  [description]
     * @param int   $level [层级]
     *
     * @return string [type]             [description]
     */
    private function adminMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            $str            .= '<li class="nav-header">菜单栏</li>';
            // $tArr = $this->arr;
            if ($this->weigh && $this->arr) {
                $tArr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
            } else {
                $tArr = $this->arr;
            }
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);

        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $level += 1;
        $nbsp  = ''; //缩进
        if ($pk > 0) {
            // $nbspStr = '&nbsp;&nbsp;';
            $nbspStr = '';
            $nbsp    = str_repeat($nbspStr, $level);
        }
        foreach ($childs as $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }

            $nextArr = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? $value[$this->childlist] : array();

            $href     = url($this->urlPrefix . $value[$this->href]);
            $icon     = !empty($value[$this->icon]) ? $value[$this->icon] : '';
            $hasSub   = '';
            $hasChild = 0;
            if (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) {
                //有子菜单
                $href     = 'javascript:;';
                $hasSub   = 'has-treeview ';
                $hasChild = 1;
            }
            $iconStr = '<i class="nav-icon ' . $icon . '"></i>';

            $activeClass = $this->checkActiveMenu($value) ? ($hasChild ? 'menu-open active' : 'active') : ''; // 激活的菜单样式

            $str .= '<li class="nav-item ' . $hasSub . ' ' . $activeClass . '"><a menu class="nav-link ' . $activeClass . '" href="' . $href . '">' . $nbsp . $iconStr . '<p>' . $value[$this->title];
            if ($hasChild) {
                $str .= '<i class="fas fa-angle-left right"></i>';
                // 右侧徽章
                $str .= $value[$this->badge] ? '<span class="right badge ' . $value[$this->badgeStyle] . '">' . $value[$this->badge] . '</span></p>' : '</p>';
            } else {
                // 右侧徽章
                $str .= $value[$this->badge] ? '</p><span class="right badge ' . $value[$this->badgeStyle] . '">' . $value[$this->badge] . '</span>' : '</p>';
            }
            $str .= '</a>';
            if ($hasChild) {
                $str .= '<ul class="nav nav-treeview">';
                // <!-- has li -->;
                $str .= $this->adminMenu($value[$this->pk], $nextArr, $level);
                $str .= '</ul>';
            }
            $str .= '</li>';

        }

        return $str;
    }

    /**
     * NAZOX 前台 顶部 nav 导航目录
     */
    public function nazoxhomeNavMenu($pk)
    {
        $str = '';
        $arr = $this->arr;
        if (empty($arr) || !is_array($arr)) {
            return '';
        }

        foreach ($arr as $item) {
            $hasChild = (isset($item[$this->childlist]) && !empty($item[$this->childlist])) ? true : false;

            $currentHref = $hasChild ? 'javascript:;' : $this->domain . url($this->urlPrefix . '/' . $item[$this->pk]); // 当前url
            $currentIcon = !empty($item[$this->icon]) ? $item[$this->icon] : '';                                                  // 当前icon

            $liClass          = $hasChild ? 'dropdown' : '';
            $liLinkClass      = $hasChild ? 'dropdown-toggle arrow-none' : '';
            $liLinkChildClass = $hasChild ? ' role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ' : '';

            $str .= '<li class="nav-item ' . $liClass . '">';
            $str .= '<a href="' . $currentHref . '" class="nav-link ' . $liLinkClass . '" id="topnav-' . $item[$this->pk] . '" ' . $liLinkChildClass . '>';
            $str .= '<i class="ri-pencil-ruler-2-line mr-2 ' . $currentIcon . '"></i>';

            $str .= $item[$this->title];
            $str .= $hasChild ? '<div class="arrow-down"></div>' : '';
            $str .= '</a>';

            if ($hasChild) {
                $str .= $this->nazoxhomeNavMenuChildNav($item);
            }
            $str .= '</li>';
        }
        return $str;
    }

    private function nazoxhomeNavMenuChildNav($item)
    {
        $str = '';
        $str .= '<div class="dropdown-menu" aria-labelledby="topnav-' . $item[$this->pk] . '">';
        foreach ($item[$this->childlist] as $key => $childlistItem) {

            $oneHasChild         = (isset($childlistItem[$this->childlist]) && !empty($childlistItem[$this->childlist])) ? true : false;
            $oneLiLinkClass      = $oneHasChild ? 'dropdown-toggle arrow-none' : '';
            $oneLiLinkChildClass = $oneHasChild ? ' role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ' : '';

            $oneCurrentHref = $oneHasChild ? 'javascript:;' : $this->domain . url($this->urlPrefix . '/' . $childlistItem[$this->pk]); // 当前url

            $str .= $oneHasChild ? '<div class="dropdown">' : '';

            $str .= '<a class="dropdown-item ' . $oneLiLinkClass . '" href="' . $oneCurrentHref . '" id="topnav-one-' . $item[$this->pk] . '" ' . $oneLiLinkChildClass . '>';
            $str .= '<i class="ri-pencil-ruler-2-line mr-2"></i>';
            $str .= $childlistItem[$this->title];
            $str .= $oneHasChild ? '<div class="arrow-down"></div>' : '';
            $str .= '</a>';

            if ($oneHasChild) {
                $str .= $this->nazoxhomeNavMenuChildNav($childlistItem);
            }
            $str .= $oneHasChild ? '</div>' : '';
        }
        $str .= '</div>';
        return $str;
    }

    /**
     * 创建 AdminLTE3 前台nav 导航目录 支持三级导航
     *
     * @param    [type]       $pk    [description]
     * @param array   $tArr  [description]
     * @param integer $level [description]
     *
     * @return string [type]              [description]
     */
    public function adminLtehomeNavMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            $tArr           = ($this->weigh && $this->arr) ? $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC) : $this->arr;
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);

        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $level += 1;
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }
            // 子对象
            $nextArr = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? $value[$this->childlist] : array();

            //有无子菜单
            $hasChild = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? 1 : 0;
            $href     = $hasChild ? 'javascript:;' : url($this->urlPrefix . '/' . $value[$this->pk])->suffix('html')->domain(true);

            if ($hasChild) {
                $str .= ($level == 1)
                    ? '<li class="nav-item dropdown"><a href="' . $href . '"  class="nav-link dropdown-toggle" id="dropdownSubMenu_' . $key . '_' . $level . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $value[$this->title] . '</a>'
                    : '<li class="dropdown-submenu ' . (($level == 2) ? 'dropdown-hover' : '') . '">  <a id="dropdownSubMenu_' . $key . '_' . $level . '" href="' . $href . '" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">' . $value[$this->title] . '</a>';

                $str .= '<ul aria-labelledby="dropdownSubMenu_' . $key . '_' . $level . '" class="dropdown-menu border-0 shadow">';

                $str .= $this->adminLtehomeNavMenu($value[$this->pk], $nextArr, $level);

                $str .= '</ul></li>';
            } else {
                $str .= ($level == 1)
                    ? '<li class="nav-item"><a href="' . $href . '" class="nav-link">' . $value[$this->title] . '</a></li>'
                    : '<li><a href="' . $href . '" class="dropdown-item">' . $value[$this->title] . '</a></li>';
            }
        }
        return $str;
    }

    private function getActiveMenuIds($menuArr = []): array
    {
        $arr = !empty($menuArr) ? $menuArr : $this->arr;
        if (!$this->activeMenu || !$arr) {
            return [];
        }
        $ids             = [];
        $activeItemsList = [];

        foreach ($arr as $value) {
            if (trim($value[$this->href], '/') == trim($this->activeMenu, '/')) {
                $ids[]             = $value[$this->pk];
                $activeItemsList[] = $value;
                break;
            }
            if (isset($value[$this->childlist]) && !empty($value[$this->childlist])) {
                list($childIds, $childItems) = $this->getActiveMenuIds($value[$this->childlist]);
                if (!empty($childIds)) {
                    $childIds[]      = $value[$this->pk];
                    $childItems[]    = $value;
                    $ids             = $childIds;
                    $activeItemsList = $childItems;
                    break;
                }
            }
        }
        return [$ids, $activeItemsList];
    }

    private function checkActiveMenu($item)
    {
        if (!in_array($item[$this->pk], $this->activeMenuIds)) { // 是否激活当前菜单
            return '';
        }

        if ($this->menuType == 'layuiadmin') {
            return 'layui-this';
        }
        if ($this->menuType == 'nazox') {
            return 'mm-active';
        }
        if ($this->menuType == 'inspinia') {
            return 'active';
        }
    }

    /**
     * 查找子数组
     *
     * @param    [type]       $pk       [description]
     * @param array $listData [description]
     *
     * @return array [type]                 [description]
     */
    protected function findChild($pk, $listData = array())
    {
        $findArr = array();
        foreach ($listData as $value) {
            if ($value['pid'] == $pk) {
                $findArr[] = $value;
                continue;
            }
            if (isset($value[$this->childlist]) && $value[$this->childlist]) {
                $result = $this->findChild($value['rule_id'], $value[$this->childlist]);
                if ($result) {
                    $findArr = array_merge($findArr, $result);
                }
            }
        }
        return $findArr;
    }

    /**
     * 清除数据
     *
     * @return Menu [type] [description]
     */
    public function clear()
    {
        $this->config          = [];
        $this->options         = [];
        $this->returnClass     = false;
        $this->arr             = [];
        $this->pk              = 'id';
        $this->pid             = 'pid';
        $this->childlist       = 'childlist';
        $this->weigh           = 'weigh';
        $this->title           = 'title';
        $this->href            = 'name';
        $this->icon            = '';
        $this->badge           = 'badge_text';
        $this->badgeStyle      = 'badge_text_style';
        $this->showchildicon   = false;
        $this->showNavIcon     = false;
        $this->menuType        = 'inspinia';
        $this->activeMenu      = '';
        $this->urlPrefix       = '';
        $this->domain          = '';
        $this->activeMenuItems = '';

        return $this;
    }

    /**
     * 获取面包屑导航
     *
     * @param    [type]       $link     [用户访问的链接]
     * @param bool   $html
     * @param bool   $clickLink
     * @param string $uri
     * @param string $text
     *
     * @return string [type]                 [description]
     */
    public function getBreadCrumb($link, $html = false, $clickLink = true, $uri = 'name', $text = 'title')
    {

        $path = strtolower(str_replace(".", "/", $link));

        $dataArr = $this->arr;

        $newAuthArr = [];
        //先设置数组的键值和名称到新数组
        foreach ($dataArr as $key => $auth) {
            $auth = obj2Arr($auth);

            $nameArr = explode('/', str_replace(".", "/", $auth[$uri]));
            if (count($nameArr) < 2) {
                $nameArr['1'] = 'index';
            }

            $auth[$uri]                   = strtolower(implode("/", $nameArr));
            $newAuthArr[$auth[$this->pk]] = $auth;

        }

        $crumb = []; //导航 数组
        foreach ($newAuthArr as $value) {

            if ($value[$uri] == $path || $value[$uri] . '/index' == $path) {
                $crumb[] = $value;
                $pid     = $value[$this->pid];

                while ($pid) {
                    $crumb[] = $newAuthArr[$pid];
                    $pid     = $newAuthArr[$pid][$this->pid];
                }
                break;
            }
        }

        $str = '';
        if ($html && !empty($crumb)) {
            $str .= '<div class="col-sm-6"><h1 class="m-0 text-dark gray-bg">';
            $str .= $crumb['0'][$text];
            $str .= '</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right gray-bg">';
        }

        $crumbCount = count($crumb) - 1;
        for ($i = $crumbCount; $i >= 0; $i--) {
            $a_link = $clickLink ? url($crumb[$i][$uri]) : 'javascript:;';
            if ($html) {
                if ($i == 0) {
                    $str .= '<li class="breadcrumb-item active">' . $crumb[$i][$text] . '</li>';
                } else {
                    $str .= '<li class="breadcrumb-item"><a href="' . $a_link . '"><i class="' . $crumb[$i][$this->icon ?? ''] . '"></i> ' . $crumb[$i][$this->title] . '</a></li>';
                }
            } else {
                $str .= $i == $crumbCount ? $crumb[$i][$text] : ' > ' . $crumb[$i][$text];
            }
        }
        return $html ? (!empty($crumb) ? $str . '</ol></div>' : '') : ($str ? $str : $path);
    }

    // 获取 nav 面包屑导航
    public function getNavBreadCrumb($id = '')
    {
        $str     = '';
        $tree    = array();
        $arrData = $this->arr;
        //第一步，将分类id作为数组key
        foreach ($arrData as $arr) {
            $tree[$arr[$this->pk]] = $arr;
        }

        $resultArr = [];
        while (isset($tree[$id])) {
            $resultArr[] = $tree[$id][$this->title];
            $id          = $tree[$id][$this->pid];
        }
        if (empty($resultArr)) {
            return '';
        }

        $crumbCount = count($resultArr) - 1;

        if ($this->menuType == 'nazox') {
            $str .= '<div class="page-title-box d-flex align-items-center justify-content-between"><h4 class="mb-0">';
            $str .= $resultArr['0'];
            $str .= '</h4>';
            $str .= '<div class="page-title-right"><ol class="breadcrumb m-0">';

            for ($i = $crumbCount; $i >= 0; $i--) {
                switch ($i) {
                    case 0:
                        $str .= '<li class="breadcrumb-item active">' . $resultArr[$i] . '</li>';
                        break;
                    default:
                        $str .= '<li class="breadcrumb-item"><a href="javascript:;">' . $resultArr[$i] . '</a></li>';
                        break;
                }
            }
            $str .= '</ol></div>';
            $str .= '</div>';
        }
        return $str;
    }

    // 获取指定节点
    public function getSelfNode($id = '', $onlyGetName = true)
    {
        $arrData = $this->arr;
        foreach ($arrData as $item) {
            if ($item[$this->pk] == $id) {
                return $onlyGetName ? $item[$this->title] : $item;
            }
        }
        return '';
    }
}
