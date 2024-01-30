<?php

namespace zxf\Tools;

/**
 * 树形结构工具类
 * 用于将一维数组转换为树形结构
 *  结构：
 *      $data = [
 *          ['id' => 1, 'pid' => 0, 'name' => 'Node 1',...],
 *          ['id' => 2, 'pid' => 1, 'name' => 'Node 2', 'weight' => 10,...],
 *      ];
 *  用法：
 *      // 使用默认配置 初始化数据
 *      $tree = Tree::instance($data);
 *      OR
 *      $tree = Tree::instance()->setData($data);
 *      // 自定义id、pid、children配置
 *      Tree::instance($data)->setId('id')->setPid('pid')->setChildlist('children')->toTree();
 *      // 自定义权重字段和排序方式
 *      Tree::instance($data)->setWeight('weight')->setSortType('desc')->toTree();
 *      // 自定义根节点id，默认为0
 *      Tree::instance($data)->setRootId(1)->toTree();
 *  接口:
 *      // 获取结构树
 *      $nodes = $tree->toTree();
 *      // 获取所有子节点的主键（包含自己）
 *      $nodes = $tree->getChildrenAndMeIds(1);
 *      // 获取所有子节点列表（包含自己）
 *      $nodes = $tree->getChildrenAndMeNodes(1);
 *      // 获取所有子节点的主键（不包含自己）
 *      $nodes = $tree->getChildrenIds(1);
 *      // 获取所有子节点列表（不包含自己）
 *      $nodes = $tree->getChildrenNodes(1);
 *      // 获取所有父节点主键(包含自己)
 *      $nodes = $tree->getParentAndMeIds(5);
 *      // 获取所有父节点列表(包含自己)
 *      $nodes = $tree->getParentAndMeNodes(5);
 *      // 获取所有父节点主键(不包含自己)
 *      $nodes = $tree->getParentIds(5);
 *      // 获取所有父节点列表(不包含自己)
 *      $nodes = $tree->getParentNodes(5);
 *      // 获取所有根节点主键
 *      $roots = $tree->getRootsIds();
 *      // 重新初始化数据
 *      $tree->reset();
 *      // 添加新节点
 *      $tree->addNode(['id' => 7, 'pid' => 0, 'name' => 'New Node']);
 *      // 删除节点
 *      $tree->removeNode(7);
 *      // 更新节点
 *      $tree->updateNode(2, ['name' => 'Updated Node']);
 */
class Tree
{
    private static $instance = null;

    private array  $data      = []; // 原始数据数组
    private string $id        = 'id'; // 节点ID字段名，默认为'id'
    private string $pid       = 'pid'; // 父节点ID字段名，默认为'pid'
    private string $weight    = 'weight'; // 根据权重排序字段，如果结构中存在此字段则降序排序，不不存在则跳过，默认为'weight'
    private string $sortType  = 'desc'; // 如果存在 $weight 的前提下 根据权重排序的方式，desc 降序 asc 升序，默认为'desc'
    private string $childlist = 'children'; // 子节点列表字段名，默认为'children'
    private int    $rootId    = 0; // 根节点ID，默认为0

    /**
     * 构造函数，接受原始数据数组
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 初始化实例
     */
    public static function instance(array $data = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($data);
        }
        return self::$instance;
    }

    /**
     * 设置数据
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置节点ID字段名
     *
     * @param string $id
     *
     * @return Tree
     */
    public function setId(string $id = 'id')
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 设置父节点ID字段名
     *
     * @param string $pid
     *
     * @return Tree
     */
    public function setPid(string $pid = 'pid')
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * 设置子节点列表字段名
     *
     * @param string $childlist
     *
     * @return Tree
     */
    public function setChildlist(string $childlist = 'childlist')
    {
        $this->childlist = $childlist;
        return $this;
    }

    /**
     * 设置排序字段
     *
     * @param string $weight
     *
     * @return $this
     */
    public function setWeight(string $weight = 'weight')
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * 存在 $weight 的前提下 根据权重排序的方式, desc 降序 asc 升序
     *
     * @param string $sortType
     *
     * @return $this
     */
    public function setSortType(string $sortType = 'desc')
    {
        $this->sortType = in_array($sortType, ['desc', 'asc']) ? strtolower($sortType) : 'desc';
        return $this;
    }

    public function setRootId(int $rootId = 0)
    {
        $this->rootId = (int)$rootId;
        return $this;
    }

    /**
     * 将原始数据转换为树形结构
     *
     * @return array
     */
    public function toTree(): array
    {
        $treeMap = array_to_tree($this->data, $this->rootId, $this->id, $this->pid, $this->childlist);

        // $rootNodes = array_filter($treeMap, fn($item) => !$item[$this->pid]); // 找出根节点（父节点为空）
        $rootNodes = array_filter($treeMap, fn($item) => $item[$this->pid] == $this->rootId); // 找出根节点（指定根节点id）

        // 使用传统方法对根节点进行排序
        $rootNodes && $this->sortNodesByWeight($rootNodes);

        return array_values($rootNodes);
    }

    /**
     * 根据权重字段对节点数组进行排序
     *
     * @param array &$nodes 节点数组
     */
    private function sortNodesByWeight(array &$nodes): array
    {
        $sortType = $this->sortType;
        // 根据权重字段对节点进行降序排序
        usort($nodes, function ($a, $b) use ($sortType) {
            if ($sortType === 'desc') {
                // 降序排序
                return (!empty($b[$this->weight]) ? $b[$this->weight] : 0) <=> (!empty($a[$this->weight]) ? $a[$this->weight] : 0);
            }
            // 升序排序
            return (!empty($a[$this->weight]) ? $a[$this->weight] : 0) <=> (!empty($b[$this->weight]) ? $b[$this->weight] : 0);
        });

        $nodes && $this->sortChildrenByWeight($nodes); // 递归调用对子节点的子节点进行排序
        return $nodes;
    }

    /**
     * 递归地对节点的子节点进行排序
     *
     * @param array &$nodes 节点数组
     */
    private function sortChildrenByWeight(array &$nodes): void
    {
        foreach ($nodes as &$node) {
            if (isset($node[$this->childlist])) {
                $children =& $node[$this->childlist];

                // 使用传统方法对子节点进行排序
                $children && $this->sortNodesByWeight($children);
            }
        }
    }

    /**
     * 获取指定节点的所有子孙节点ID(包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getChildrenAndMeIds(int $id): array
    {
        $descendants = [];

        foreach ($this->data as $item) {
            if ($this->isDescendant($item[$this->id], $id)) {
                $descendants[] = $item[$this->id];
            }
        }
        return $descendants;
    }

    /**
     * 获取指定节点的所有子孙节点列表(包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getChildrenAndMeNodes(int $id): array
    {
        $descendants = [];

        foreach ($this->data as $item) {
            if ($this->isDescendant($item[$this->id], $id)) {
                $descendants[] = $item;
            }
        }
        return $descendants;
    }

    /**
     * 获取指定节点的所有子孙节点ID(不包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getChildrenIds(int $id): array
    {
        $descendants = [];

        foreach ($this->data as $item) {
            if ($this->isDescendant($item[$this->id], $id, false)) {
                $descendants[] = $item[$this->id];
            }
        }
        return $descendants;
    }

    /**
     * 获取指定节点的所有子孙节点列表(不包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getChildrenNodes(int $id): array
    {
        $descendants = [];

        foreach ($this->data as $item) {
            if ($this->isDescendant($item[$this->id], $id, false)) {
                $descendants[] = $item;
            }
        }
        return $descendants;
    }

    /**
     * 判断指定节点是否是指定父节点的子孙节点
     *
     * @param int  $itemId      要查询的节点id
     * @param int  $parentId    要查询的父节点id
     * @param bool $includeSelf 是否包含自己
     *
     * @return bool
     */
    private function isDescendant(int $itemId, int $parentId, bool $includeSelf = true): bool
    {
        foreach ($this->data as $item) {
            if ($item[$this->id] === $itemId) {// 先找到要查询的那个节点

                if ($includeSelf ? $item[$this->id] === $parentId : $item[$this->pid] === $parentId) {
                    return true;
                }

                // 一直向上找父节点，判断父节点是否存在要查询的指定 id父节点
                if ($this->isDescendant($item[$this->pid], $parentId, $includeSelf)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取指定节点的所有父节点ids(包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getParentAndMeIds(int $id): array
    {
        $parents   = [];
        $currentId = $id;

        while ($currentId !== 0) {
            foreach ($this->data as $item) {
                // 找到当前节点
                if ($item[$this->id] === $currentId) {
                    $parents[] = $currentId;
                    $currentId = $item[$this->pid];
                    break;
                }
            }
        }
        return array_reverse($parents);
    }

    /**
     * 获取指定节点的所有父节点列表(包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getParentAndMeNodes(int $id): array
    {
        $parents   = [];
        $currentId = $id;

        while ($currentId !== 0) {
            foreach ($this->data as $item) {
                // 找到当前节点
                if ($item[$this->id] === $currentId) {
                    $parents[] = $item;
                    $currentId = $item[$this->pid];
                    break;
                }
            }
        }
        return $parents;
    }

    /**
     * 获取指定节点的所有父节点ids(不包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getParentIds(int $id): array
    {
        $parents   = [];
        $currentId = $id;

        while ($currentId !== 0) {
            foreach ($this->data as $item) {
                // 找到当前节点
                if ($item[$this->id] === $currentId) {
                    if ($currentId !== $id) {
                        $parents[] = $currentId;
                    }
                    $currentId = $item[$this->pid];
                    break;
                }
            }
        }
        return array_reverse($parents);
    }

    /**
     * 获取指定节点的所有父节点列表(不包含自己)
     *
     * @param int $id
     *
     * @return array
     */
    public function getParentNodes(int $id): array
    {
        $parents   = [];
        $currentId = $id;

        while ($currentId !== 0) {
            foreach ($this->data as $item) {
                // 找到当前节点
                if ($item[$this->id] === $currentId) {
                    if ($currentId !== $id) {
                        $parents[] = $item;
                    }
                    $currentId = $item[$this->pid];
                    break;
                }
            }
        }
        return $parents;
    }

    /**
     * 获取所有的根节点ids
     *
     * @return array
     */
    public function getRootsIds(): array
    {
        $roots = [];

        foreach ($this->data as $item) {
            if ($item[$this->pid] === $this->rootId) {
                $roots[] = $item[$this->id];
            }
        }

        return $roots;
    }

    /**
     * 重置所有属性并清空数据
     *
     * @return $this
     */
    public function reset()
    {
        $this->id        = 'id';
        $this->pid       = 'pid';
        $this->weight    = 'weight';
        $this->sortType  = 'desc';
        $this->rootId    = 0;
        $this->childlist = 'children';
        // $this->data      = [];

        return $this;
    }

    /**
     * 添加新节点
     *
     * @param array $node
     *
     * @return $this
     */
    public function addNode(array $node)
    {
        $this->data[] = $node;
        return $this;
    }

    /**
     * 通过主键id删除节点
     *
     * @param int $id
     *
     * @return $this
     */
    public function removeNode(int $id)
    {
        $this->data = array_filter($this->data, fn($item) => $item[$this->id] !== $id);
        return $this;
    }

    /**
     * 通过主键id更新节点
     *
     * @param int   $id
     * @param array $newData
     *
     * @return $this
     */
    public function updateNode(int $id, array $newData)
    {
        foreach ($this->data as &$item) {
            if ($item[$this->id] === $id) {
                $item = array_merge($item, $newData);
                break;
            }
        }
        return $this;
    }
}
