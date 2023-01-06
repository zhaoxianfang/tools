<?php

namespace zxf\extend;

/**
 * 获取菜单
 */
// demo
// Menu::instance()->init($ruleList)->setActiveMenu($urlLink)->createMenu(0);
// Menu::instance()->init($munuList)->setActiveMenu($urlLink)->setMenuType('inspinia')->createMenu(0);
// 顶部导航
// Menu::instance()->init($classifyList)->setTitle('name')->setUrlPrefix('/classify')->setActiveMenu($urlLink)->createMenu(0, 'home');
// 面包屑导航
// Menu::instance()->init($menuList, 'id', 'pid', 0, false)->getBreadCrumb('/admin/index/index', $raturnhtml = true, false);
// 菜单转换为视图
// Menu::instance()->init($arrList)->setWeigh()->getTree();

class Menu
{
    protected $arr           = [];
    protected $pk            = 'id';
    protected $pid           = 'pid';
    protected $childlist     = 'childlist';
    protected $weigh         = 'weigh'; //权重
    protected $title         = 'title'; //Tree 展示的作用字段
    protected $badge         = 'badge_text'; //badge 图标
    protected $badgeStyle    = 'badge_text_style'; //badge 图标 样式
    protected $showchildicon = false; //子级菜单显示icon小图标
    protected $showNavIcon   = false; //前台nav 一级导航是否显示icon小图标
    protected $menuType      = 'nazox'; //目录类型 adminlte|layuiadmin|nazox|inspinia

    protected static $instance;
    //默认配置
    protected $config  = [];
    public    $options = [];
    //是否返回 $this
    protected $returnClass = false;
    //触发的菜单
    protected $activeMenu = '';
    //url地址前缀
    protected $urlPrefix = '/classify';

    protected $domain = ''; // 域名

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    protected $icon = array(' │', ' ├', ' └');

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    // 初始化参数，防止 调用 Menu 的过程中 有的 参数被篡改 导致 之后的调用参数发生错乱
    private function initConfigParam()
    {
        $this->arr = [];
        $this->pk = 'id';
        $this->pid = 'pid';
        $this->childlist = 'childlist';
        $this->weigh = 'weigh'; //权重
        $this->title = 'title'; //Tree 展示的作用字段
        $this->badge = 'badge_text'; //badge 图标
        $this->badgeStyle = 'badge_text_style'; //badge 图标 样式
        $this->showchildicon = false; //子级菜单显示icon小图标
        $this->showNavIcon = false; //前台nav 一级导航是否显示icon小图标
        $this->menuType = 'nazox'; //目录类型 adminlte|layuiadmin|nazox|inspinia

        //默认配置
        $this->config = [];
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
     * @access public
     * @param array $options 参数
     * @return Tree
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
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     * )
     */
    public function init($arr = [], $pk = 'id', $pid = 'pid', $rootId = 0, $initTree = true, $childlist = 'childlist')
    {
        $this->initConfigParam();

        $this->arr = $arr;
        $pk ? $this->pk = $pk : 'id';
        $pid ? $this->pid = $pid : 'pid';
        $childlist ? $this->childlist = $childlist : $this->childlist;

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
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-16
     * @param string $flag [description]
     */
    public function setDomain($domain = '')
    {
        $this->domain = $domain; // 域名
        return $this;
    }

    /**
     * 是否返回 $this 配合 getTreeTwo 用
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-16
     * @param string $flag [description]
     */
    public function setReturn($flag = false)
    {
        $this->returnClass = $flag ? true : false;
        return $this;
    }

    /**
     * 设置后台菜单是否包含icon 小图标
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param boolean $flag [description]
     */
    public function setAdminMenuIcon($flag = false)
    {
        $this->showchildicon = $flag ? true : false;
        return $this;
    }

    /**
     * 设置权重名
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-12
     * @param boolean $str [description]
     */
    public function setWeigh($str = '')
    {
        $this->weigh = $str;
        return $this;
    }

    /**
     * 设置tree 作用的字段
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @param string $name [description]
     */
    public function setTitle($str = 'title')
    {
        $this->title = $str;
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
            // $data[$row[$id]][$name] = $row[$name];
            $data[$row[$id]] = $row;
            $data[$row[$pid]][$this->childlist][$row[$id]] = &$data[$row[$id]];
        }
        return isset($data[$rootId][$this->childlist]) ? $data[$rootId][$this->childlist] : [];
    }

    /**
     * 获取数 TREE
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-14
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
        $tree = array();
        $usePkArr = []; ////记录被处理过的pid
        //第一步，将分类id作为数组key,并创建children单元
        foreach ($arrData as $arr) {
            $arr = object_to_array($arr);
            $tree[$arr[$this->pk]] = $arr;
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
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-28
     * @return   [type]       [description]
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
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-28
     * @param string $arrData [description]
     * @param string $lv [级别 跟级为0]
     * @return   [type]                [description]
     */
    private function reduce($arrData = '', $lv = 0)
    {
        $listArr = array();
        if (!$arrData) {
            return array();
        }
        $countArr = count($arrData) - 1;
        foreach ($arrData as $key => $value) {

            if ($lv > 0) {
                if ($key == $countArr) {
                    $value[$this->title] = str_repeat($this->icon['0'], $lv - 1) . $this->icon['2'] . $value[$this->title];
                } else {
                    $value[$this->title] = str_repeat($this->icon['0'], $lv - 1) . $this->icon['1'] . $value[$this->title];
                }
            }
            $listArr[] = $value;
            if (isset($value[$this->childlist]) && $value[$this->childlist]) {
                if ($this->weigh) {
                    $value[$this->childlist] = $this->my_sort($value[$this->childlist], $this->weigh, SORT_DESC, SORT_NUMERIC);
                }

                $lv++;
                $childArr = $this->reduce($value[$this->childlist], $lv);

                // if (isset($value[$this->childlist]['0'][$this->childlist]) || !$this->weigh) {
                $listArr = array_merge($listArr, $childArr);
                // } else {
                //     $listArr = array_merge($listArr, $this->my_sort($childArr, $this->weigh, SORT_DESC));
                // }
                $lv--;

            }
        }
        return $listArr;

    }

    /**
     * 自定义 数组排序
     * @Author   ZhaoXianFang
     * @DateTime 2017-09-11
     * @param    [type]       $arrays     [被排序数组]
     * @param    [type]       $sort_key   [被排序字段]
     * @param    [type]       $sort_order [排序方式]
     * @param    [type]       $sort_type  [排序类型]
     * @return   [type]                   [description]
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
     * @Author   ZhaoXianFang
     * @DateTime 2019-01-10
     * @param string $menuType [默认adminlte目录]
     *                                  仅支持 adminlte|layuiadmin|nazox|inspinia
     */
    public function setMenuType($menuType = 'nazox')
    {
        $this->menuType = $menuType;
        return $this;
    }

    /**
     * [createMenu 创建目录] [默认adminlte风格目录--目前仅支持 adminlte|layuiadmin|nazox ]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    [type]       $pk    [顶级pid 一般为0]
     * @param string $scope [作用域，admin(后台)、home(前台)]
     * @return   [type]              [description]
     */
    public function createMenu($pk = 0, $scope = 'admin')
    {
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

            $currentHref = $hasArrow ? 'javascript:;' : url($item['name']); // 当前url
            $currentIcon = $item['icon']; // 当前url

            $isActive = $this->checkactiveMenu($item['name'], $hasArrow); // 'active'; // 是否激活当前菜单
            $isShowUl = ($hasArrow && ($isActive == 'active')) ? 'in' : ''; // in 是否展开当前子菜单ul

            $str .= '<li class="' . $isActive . '">';
            // $str .= '<a href="' . $currentHref . '" aria-expanded="' . ($isActive ? 'true' : 'false') .  '">';
            $str .= '<a href="' . $currentHref . '">';
            $str .= '<i class="fa ' . $currentIcon . '"></i>';

            $str .= '<span class="nav-label">' . $item[$this->title] . '</span>';
            // 子菜单
            $str .= $hasArrow ? '<span class="fa arrow"></span>' : '';

            // && // 右侧图标
            $item['badge_text'] && (
            $str .= '<span class="float-right label ' . ($item['badge_text_style'] ? $item['badge_text_style'] : 'label-info') . '">' . $item['badge_text'] . '</span>'
            );

            $str .= '</a>';
            // 子菜单
            $str .= $hasArrow ? '<ul class="nav nav-second-level collapse ' . $isShowUl . '" aria-expanded="false">' : '';

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

        foreach ($arr as $key => $item) {
            $hasArrow = (isset($item[$this->childlist]) && !empty($item[$this->childlist])) ? true : false;

            $currentHref = $hasArrow ? 'javascript:;' : url($item['name']); // 当前url
            $currentIcon = $item['icon']; // 当前url

            $isActive = $this->checkactiveMenu($item['name'], $hasArrow); // 'mm-active'; // 是否激活当前菜单
            $isShowUl = ($hasArrow && $isActive == 'mm-active') ? 'mm-show' : ''; // mm-show 是否展开当前子菜单ul

            $str .= '<li class="' . $isActive . '">';
            $str .= '<a href="' . $currentHref . '" class="' . ($hasArrow ? 'has-arrow ' : ' ') . $isActive . ' waves-effect">';
            $str .= '<i class="fa ' . $currentIcon . '"></i>';
            // && // 右侧图标
            $item['badge_text'] && (
            $str .= '<span class="badge badge-pill ' . ($item['badge_text_style'] ? $item['badge_text_style'] : 'badge-info') . ' float-right">' . $item['badge_text'] . '</span>'
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
     * @Author   ZhaoXianFang
     * @DateTime 2019-01-10
     * @param    [type]       $pk    [description]
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
            $icon = $menu['icon'];
            $layHref = (isset($menu[$this->childlist]) && !empty($menu[$this->childlist])) ? '' : 'lay-href="' . url($menu['name']) . '"';
            $activeMenu = $layHref ? $this->checkactiveMenu($menu['name']) : '';
            $str .= '<li data-name="' . $menu['name'] . '" class="layui-nav-item ' . $activeMenu . '"><a href="javascript:;" lay-tips="' . $menu['title'] . '" ' . $layHref . ' lay-direction="2"><i class="' . $icon . '"></i><cite>' . $menu['title'] . '</cite></a>' . $this->layuiChildList($menu) . '</li>';
        }
        return $str;
    }

    /**
     * [layuiAdmin 目录子列表]
     * @Author   ZhaoXianFang
     * @DateTime 2019-01-10
     * @param string $parentData [description]
     * @return   [type]                   [description]
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
            $layHref = (isset($page[$this->childlist]) && !empty($page[$this->childlist])) ? '' : 'lay-href="' . url($page['name']) . '"';
            $activeMenu = $layHref ? $this->checkactiveMenu($page['name']) : '';
            $str .= '<dl class="layui-nav-child"><dd data-name="' . $page['name'] . '" class=" ' . $activeMenu . '"><a href="javascript:;" ' . $layHref . ' >' . $page['title'] . '</a>' . $this->layuiChildList($page) . '</dd></dl>';
        }
        return $str;
    }

    /**
     * 创建后台目录 [adminLte 目录]
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @param    [type]       $pk   [description]
     * @param array $tArr [description]
     * @param array $level [层级]
     * @return   [type]             [description]
     */
    private function adminMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            $str .= '<li class="nav-header">菜单栏</li>';
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
        $nbsp = ''; //缩进
        if ($pk > 0) {
            // $nbspStr = '&nbsp;&nbsp;';
            $nbspStr = '';
            $nbsp = str_repeat($nbspStr, $level);
        }
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }

            $nextArr = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? $value[$this->childlist] : array();

            $href = url($this->urlPrefix . $value['name']);
            $icon = $value['icon'];
            $hasSub = '';
            $hasChild = 0;
            if (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) {
                //有子菜单
                $href = 'javascript:;';
                $hasSub = 'has-treeview ';
                $hasChild = 1;
            }
            $iconStr = '';
            //是否显示子级icon 图标
            // if ($level == 1 || $this->showchildicon) {
            $iconStr = '<i class="nav-icon ' . $icon . '"></i>';
            // }

            $activeMenu = $this->checkactiveMenu($value['name'], $hasChild);
            $str .= '<li class="nav-item ' . $hasSub . ' ' . $activeMenu . '"><a menu class="nav-link ' . $activeMenu . '" href="' . $href . '">' . $nbsp . $iconStr . '<p>' . $value[$this->title];
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
     * @Author   ZhaoXianFang
     */
    public function nazoxhomeNavMenu($pk)
    {
        $str = '';
        $arr = $this->arr;
        if (empty($arr) || !is_array($arr)) {
            return '';
        }

        foreach ($arr as $key => $item) {
            $hasChild = (isset($item[$this->childlist]) && !empty($item[$this->childlist])) ? true : false;

            $currentHref = $hasChild ? 'javascript:;' : $this->domain . url($this->urlPrefix . '/' . $item[$this->pk]); // 当前url
            $currentIcon = $item['icon'] ?? '';// 当前icon

            $liClass = $hasChild ? 'dropdown' : '';
            $liLinkClass = $hasChild ? 'dropdown-toggle arrow-none' : '';
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

            $oneHasChild = (isset($childlistItem[$this->childlist]) && !empty($childlistItem[$this->childlist])) ? true : false;
            $oneLiLinkClass = $oneHasChild ? 'dropdown-toggle arrow-none' : '';
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
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    [type]       $pk    [description]
     * @param array $tArr [description]
     * @param integer $level [description]
     * @return   [type]              [description]
     */
    public function adminLtehomeNavMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
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
        $level += 1;
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }
            // 子对象
            $nextArr = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? $value[$this->childlist] : array();

            //有无子菜单
            $hasChild = (isset($value[$this->childlist]) && count($value[$this->childlist]) > 0) ? 1 : 0;
            $href = $hasChild ? 'javascript:;' : url($this->urlPrefix . '/' . $value[$this->pk])->suffix('html')->domain(true);

            // $activeMenu = $this->checkactiveMenu($value['name'], $hasChild);
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

    /**
     * 检测是否 激活该菜单
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-30
     * @param string $link [数据库记录的地址]
     * @param string $hasChild [有子菜单？]
     * @return   [type]                 [description]
     */
    private function checkactiveMenu($link = '', $hasChild = 0)
    {
        if (!$this->activeMenu || !$link) {
            return '';
        }
        $link = str_ireplace('.', '/', $link);

        $linkArr = explode('/', strtolower(trim($link, '/'))); //数据库获取
        $setLinkArr = $this->activeMenu ? explode('/', strtolower($this->activeMenu)) : []; //当前控制器与方法
        $activeStr = '';

        // 获取url所在模块
        try {
            // thinkphp 使用
            if ($linkArr['0'] != app('http')->getName()) {
                array_unshift($linkArr, app('http')->getName());
            }
        } catch (\Exception $e) {
            // laravel
            list($modules, $controller, $method) = get_laravel_route();
            if ($linkArr['0'] != $modules) {
                array_unshift($linkArr, $modules);
            }
        }

        // 获取url所在模块
        try {
            // thinkphp 使用
            if ($setLinkArr['0'] != app('http')->getName()) {
                array_unshift($setLinkArr, app('http')->getName());
            }
        } catch (\Exception $e) {
            // laravel
            if ($setLinkArr['0'] != $modules) {
                array_unshift($setLinkArr, $modules);
            }
        }

        // 使两个数组的长度一致 使用index 填充
        // if (($setCount = count($setLinkArr)) != ($linkCount = count($linkArr))) {
        //     for ($i = 0; $i < abs($setCount - $linkCount); $i++) {
        //         $setCount < $linkCount ? array_push($setLinkArr, "index") : array_push($linkArr, "index");
        //     }
        // }

        $flag = false;
        foreach ($linkArr as $key => $node) {
            if (isset($setLinkArr[$key]) && ($node == $setLinkArr[$key])) {
                $flag = true;
            } else {
                return '';
            }
        }

        //菜单样式
        if ($this->menuType == 'adminlte') {
            if ($flag || $hasChild) {
                return $hasChild ? 'menu-open active' : 'active';
            }
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
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @param    [type]       $pk       [description]
     * @param array $listData [description]
     * @return   [type]                 [description]
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
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @return   [type]       [description]
     */
    public function clear()
    {
        $this->config = [];
        $this->options = [];
        $this->returnClass = '';
        $this->arr = [];
        $this->pk = 'id';
        $this->pid = 'pid';
        return $this;
    }

    /**
     * 获取面包屑导航
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-07
     * @param    [type]       $link     [用户访问的链接]
     * @param    [type]       $html     [是否返回html导航]
     * @param    [type]       $clickLink[能否点击a标签链接]
     * @param    [type]       $uri      [导航的 uri 地址字段]
     * @param    [type]       $text     [面包屑导航展示文字字段]
     * @return   [type]                 [description]
     */
    public function getBreadCrumb($link, $html = false, $clickLink = true, $uri = 'name', $text = 'title')
    {

        $path = strtolower(str_replace(".", "/", $link));
        // $path = strtolower($link);

        $dataArr = $this->arr;

        $newAuthArr = [];
        //先设置数组的键值和名称到新数组
        foreach ($dataArr as $key => $auth) {
            $auth = object_to_array($auth);
            // str_replace(".","/",$auth[$uri]);

            $nameArr = explode('/', str_replace(".", "/", $auth[$uri]));
            if (count($nameArr) < 2) {
                $nameArr['1'] = 'index';
            }

            $auth[$uri] = strtolower(implode("/", $nameArr));
            $newAuthArr[$auth[$this->pk]] = $auth;

        }

        $crumb = []; //导航 数组
        foreach ($newAuthArr as $key => $value) {

            if ($value[$uri] == $path || $value[$uri] . '/index' == $path) {
                $crumb[] = $value;
                $pid = $value[$this->pid];

                while ($pid) {
                    $crumb[] = $newAuthArr[$pid];
                    $pid = $newAuthArr[$pid][$this->pid];
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
                    $str .= '<li class="breadcrumb-item"><a href="' . $a_link . '"><i class="' . $crumb[$i]['icon'] . '"></i> ' . $crumb[$i][$this->title] . '</a></li>';
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
        $str = '';
        $tree = array();
        $arrData = $this->arr;
        //第一步，将分类id作为数组key
        foreach ($arrData as $arr) {
            $tree[$arr[$this->pk]] = $arr;
        }

        $resultArr = [];
        while (isset($tree[$id])) {
            $resultArr[] = $tree[$id][$this->title];
            $id = $tree[$id][$this->pid];
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
        foreach ($arrData as $key => $item) {
            if ($item[$this->pk] == $id) {
                return $onlyGetName ? $item[$this->title] : $item;
            }
        }
        return '';
    }

}
