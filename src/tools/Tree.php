<?php

namespace zxf\tools;

class Tree
{
    protected $originalData = []; // 传递进来的原始数组数据
    protected $tree         = []; // 处理后的数组数据
    protected $treeArr      = []; // 挂载在树上的数组【二维数组】
    protected $tempArr      = []; // 处理tree时候会用来做临时数据存储
    protected $pk           = 'id';
    protected $pid          = 'pid';
    protected $childName    = 'childlist';

    protected static $instance;

    public function __construct()
    {

    }

    /**
     * 初始化
     * @return Tree
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
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
    public function init($arr = [], $pk = 'id', $pid = 'pid', $childName = 'childlist')
    {
        $this->clear();

        $this->originalData = $arr;
        $this->treeArr      = [];
        $this->tree         = [];
        $this->pk           = $pk ?? 'id';
        $this->pid          = $pid ?? 'pid';
        $this->childName    = $childName ?? 'childlist';

        $this->refreshTree();

        return $this;
    }

    /**
     * 将数据格式化成树形结构
     */
    private function refreshTree()
    {
        $items = empty($this->treeArr) ? $this->originalData : $this->treeArr;
        $tree  = array(); //格式化好的树
        foreach ($items as $item) {
            if (isset($items[$item[$this->pid]])) {
                $items[$item[$this->pid]][$this->childName][] = &$items[$item[$this->pk]];
            } else {
                $tree[] = &$items[$item[$this->pk]];
            }
        }
        $this->tree = $tree;
        $this->treeToArr();
        return $this;
    }

    /**
     * 获取生成的树
     * @return array
     */
    public function getTree()
    {
        if (empty($this->tree)) {
            $this->refreshTree();
        }
        return $this->tree;
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
    private function sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (!is_array($arrays)) {
            return false;
        }
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$sort_key];
            } else {
                return false;
            }
        }
        if (empty($key_arrays)) {
            return $arrays;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    /**
     * 清除数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @return   [type]       [description]
     */
    public function clear()
    {
        $this->originalData = []; // 传递进来的原始数组数据
        $this->tree         = []; // 处理后的数组数据
        $this->treeArr      = []; // 处理后的数组数据
        $this->tempArr      = [];
        $this->pk           = 'id';
        $this->pid          = 'pid';
        $this->childName    = 'childlist';

        return $this;
    }

    /**
     * 用指定字段作为键
     *
     * @param string $field
     *
     * @return array
     */
    public function keyBy(string $field = 'id')
    {
        return array_column($this->treeArr, null, $field);
    }

    /**
     * 仅返回树中的指定字段
     * @param $field
     * @return array
     */
    public function pluck($field = 'id')
    {
        return array_column($this->treeArr, $field);
    }

    /**
     *  把$node 挂载到 pk 为 $pid的 节点上
     * @param $pid
     * @param $node
     */
    public function addNode($pid, $node)
    {
        if (!isset($node[$this->pk])) {
            throw new \Exception('缺少' . $this->pk . '字段');
        }
        $res = $this->keyBy($this->pk);
        if ($pid != 0 && !isset($res[$pid])) {
            throw new \Exception('需要挂载的位置不存在');
        }
        if (isset($res[$item[$this->pk]])) {
            throw new \Exception('添加的节点id已经存在');
        }
        $node[$this->pid] = $pid;
        $this->treeArr[]  = $node;
        $this->tree       = [];
        return $this;
    }

    /**
     * 删除指定id的节点
     * @param $id
     * @return $this|false
     */
    public function delNode($id)
    {
        $res = $this->keyBy($this->pk);
        if (!isset($res[$id])) {
            return false;
        }
        unset($res[$id]);
        foreach ($res as $item) {
            $this->treeArr[] = $item;
        }
        $this->tree = [];
        return $this;
    }

    /**
     * 修改指定节点
     * @param $id
     * @param $node
     * @return $this
     * @throws \Exception
     */
    public function changeNode($id, $node)
    {
        if (!isset($node[$this->pk])) {
            throw new \Exception('缺少' . $this->pk . '字段');
        }
        $res = $this->keyBy($this->pk);
        if (!isset($res[$node[$this->pk]])) {
            throw new \Exception('修改的节点不存在');
        }
        foreach ($res as $key => $item) {
            if ($key == $id) {
                $node[$this->pk]  = $id;
                $node[$this->pid] = $item[$this->pid];
                $this->treeArr[]  = $node;
            } else {
                $this->treeArr[] = $item;
            }
        }
        $this->tree = [];
        return $this;
    }

    /**
     * 遍历修改tree
     * @return $this
     */
    private function traverseTree($callback)
    {
        $data = $this->treeArr;
        foreach ($data as &$item) {
            $callback && $callback($item);
        }
        $this->refreshTree();
        return $this;
    }

    /**
     * 把树转化为 二维数组 [不在树在的原始数据节点都消失]
     * 作用，方便查询和修改树节点
     * @param $arr
     * @return array|mixed
     */
    private function treeToArr($arr = [])
    {
        $tree = empty($arr) ? $this->tree : $arr;
        $list = [];
        foreach ($tree as $item) {
            $child = $item[$this->childName] ?? [];
            unset($item[$this->childName]);
            $list[] = $item;
            if (!empty($child)) {
                $childList = $this->treeToArr($child);
                if (!empty($childList)) {
                    $list = array_merge($list, $childList);
                }
            }
        }
        $this->treeArr = $list;
        return $this;
    }

    /**
     * 查找指定id的节点树
     * @param $id
     * @return array|mixed
     */
    public function find($id)
    {
        if ($id < 1) {
            return $this->getTree();
        }
        $res = $this->keyBy($this->pk);
        if (!isset($res[$id])) {
            return [];
        }

        $tree = empty($this->tempArr) ? $this->getTree() : $this->tempArr;
        foreach ($tree as $item) {
            if ($item[$this->pk] == $id) {
                return $item;
            }
            $this->tempArr = $item[$this->childName] ?? [];
            if (!empty($this->tempArr)) {
                return $this->find($id);
            }
        }
        return [];
    }

    /**
     * 查找指定id的子节点树
     * @param $id
     * @return array|mixed
     */
    public function childTree($id)
    {
        $res = $this->keyBy($this->pk);
        if (!isset($res[$id])) {
            return [];
        }
        $tree = empty($this->tempArr) ? $this->getTree() : $this->tempArr;
        foreach ($tree as $item) {
            if ($item[$this->pk] == $id) {
                return $item[$this->childName] ?? [];
            }
            $this->tempArr = $item[$this->childName] ?? [];
            if (!empty($this->tempArr)) {
                return $this->childTree($id);
            }
        }
        return [];
    }

    /**
     * 查找指定id的父节点树
     * @param $id
     * @return array|mixed
     */
    public function parentTree($id)
    {
        if ($id == 0) {
            return $this->getTree();
        }
        $res = $this->keyBy($this->pid);
        if (!isset($res[$id])) {
            return []; // 无此节点
        }
        return $this->find($res[$id][$this->pid]);
    }
}
