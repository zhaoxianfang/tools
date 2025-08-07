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
 *      // 克隆: 可以防止连续的链式调用导致 data 数据变更带来的问题
 *      $tree->clone();
 *      // 获取所有数据id
 *      $ids = $tree->getAllIds();
 *      // 获取字段值集合
 *      $ages = $tree->pluck('age');  // 获取所有age值
 *      // 将树形结构扁平化
 *      $treeData = $tree->toTree();
 *      $flattened = $tree->flatten($treeData);
 *  条件查询：
 *      // 基础形式
 *      $tree->where('age', 18)->where('score', '>', 60)->where('name', 'like', '%张%');
 *      // 数组形式
 *      $tree->where(['age' => 18])->where([['age', '>', 20],['status', 1]]);
 *      // 闭包形式
 *      $tree->where(fn($item) => $item['age'] > 18 && $item['score'] > 80);
 *      // 空值判断
 *      $tree->whereNull('name');
 *      $tree->whereNotNull('name');
 *      // 范围查询
 *      $tree->whereIn('score', [70,99,88]);
 *      $tree->whereNotIn('score', [70,99,88]);
 *      $tree->whereBetween('score', [70,99]);
 *      $tree->whereNotBetween('score', [70,99]);
 *      // 模糊匹配
 *      $tree->whereLike('name', '%张%');       // 查询name包含"张"的记录
 *  字段修改：
 *      // 给满足 $where 条件的节点数据添加/更新字段属性
 *      $tree->addField($where, function() {
 *          return ['is_man' => 1, 'gender_text' => '男性'];
 *      });
 *      // 为满足条件的节点移除字段属性
 *      $tree->removeField(['gender' => 1], function() {
 *          return 'is_man'; // 删除 is_man 字段属性
 *          // 或者返回数组 ['is_man', 'gender_text']
 *      });
 *      // 给满足 $where 条件的[第一个]节点及其所有父节点 添加/更新字段属性
 *      $tree->addFieldWithParentIds($where, function() {
 *          return ['is_man' => 1, 'gender_text' => '男性'];
 *      });
 *      // 给满足 $where 条件的[第一个]节点及其所有子节点 移除字段属性
 *      $tree->removeFieldWithParentIds(['gender' => 1], function() {
 *          return 'is_man'; // 删除 is_man 字段属性
 *          // 或者返回数组 ['is_man', 'gender_text']
 *      });
 */
class Tree
{
    private static $instance = null;

    private array $data = []; // 原始数据数组

    private string $id = 'id'; // 节点ID字段名，默认为'id'

    private string $pid = 'pid'; // 父节点ID字段名，默认为'pid'

    private string $weight = 'weight'; // 根据权重排序字段，如果结构中存在此字段则降序排序，不不存在则跳过，默认为'weight'

    private string $sortType = 'desc'; // 如果存在 $weight 的前提下 根据权重排序的方式，desc 降序 asc 升序，默认为'desc'

    private string $childlist = 'children'; // 子节点列表字段名，默认为'children'

    private int $rootId = 0; // 根节点ID，默认为0

    /**
     * 构造函数，接受原始数据数组
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
        if (! isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static($data);
        }

        return self::$instance;
    }

    /**
     * 设置数据
     *
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
        $this->rootId = (int) $rootId;

        return $this;
    }

    /**
     * 将原始数据转换为树形结构
     */
    public function toTree(): array
    {
        $treeMap = array_to_tree($this->data, $this->rootId, $this->id, $this->pid, $this->childlist);

        // $rootNodes = array_filter($treeMap, fn($item) => !$item[$this->pid]); // 找出根节点（父节点为空）
        $rootNodes = array_filter($treeMap, fn ($item) => $item[$this->pid] == $this->rootId); // 找出根节点（指定根节点id）

        // 使用传统方法对根节点进行排序
        $rootNodes && $this->sortNodesByWeight($rootNodes);

        return array_values($rootNodes);
    }

    /**
     * 根据权重字段对节点数组进行排序
     *
     * @param  array  &$nodes  节点数组
     */
    private function sortNodesByWeight(array &$nodes): array
    {
        $sortType = $this->sortType;
        // 根据权重字段对节点进行降序排序
        usort($nodes, function ($a, $b) use ($sortType) {
            if ($sortType === 'desc') {
                // 降序排序
                return (! empty($b[$this->weight]) ? $b[$this->weight] : 0) <=> (! empty($a[$this->weight]) ? $a[$this->weight] : 0);
            }

            // 升序排序
            return (! empty($a[$this->weight]) ? $a[$this->weight] : 0) <=> (! empty($b[$this->weight]) ? $b[$this->weight] : 0);
        });

        $nodes && $this->sortChildrenByWeight($nodes); // 递归调用对子节点的子节点进行排序

        return $nodes;
    }

    /**
     * 递归地对节点的子节点进行排序
     *
     * @param  array  &$nodes  节点数组
     */
    private function sortChildrenByWeight(array &$nodes): void
    {
        foreach ($nodes as &$node) {
            if (isset($node[$this->childlist])) {
                $children = &$node[$this->childlist];

                // 使用传统方法对子节点进行排序
                $children && $this->sortNodesByWeight($children);
            }
        }
    }

    /**
     * 获取指定节点的所有子孙节点ID(包含自己)
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
     * @param  int  $itemId  要查询的节点id
     * @param  int  $parentId  要查询的父节点id
     * @param  bool  $includeSelf  是否包含自己
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
     */
    public function getParentAndMeIds(int $id): array
    {
        $parents = [];
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
     */
    public function getParentAndMeNodes(int $id): array
    {
        $parents = [];
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
     */
    public function getParentIds(int $id): array
    {
        $parents = [];
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
     */
    public function getParentNodes(int $id): array
    {
        $parents = [];
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

    /**********************************************
     *              条件查询方法组 开始                 *
     **********************************************/
    /**
     * 基础条件查询
     * 支持三种形式：
     * 1. 基础形式:
     *    where('age', 19)
     *    where('age', '=', 19)
     *    where('age', '>', 19)
     *    where('name', 'like', '%三%')
     *
     * 2. 数组形式:
     *    where(['age' => 19])
     *    where([
     *      ['age', '>', 20],
     *      ['status', 1]
     *    ])
     *
     * 3. 闭包形式:
     *    where(fn($item) => $item['age'] > 18)
     *
     * @param  mixed  ...$args
     * @return $this
     */
    public function where(...$args): self
    {
        if (empty($args)) {
            return $this;
        }

        // 闭包形式处理
        if ($args[0] instanceof \Closure) {
            $this->data = array_filter($this->data, $args[0]);

            return $this;
        }

        // 数组形式处理
        if (is_array($args[0])) {
            $conditions = $args[0];

            // 关联数组转换为条件数组
            if (! array_is_list($conditions)) {
                $conditions = array_map(
                    fn ($k, $v) => is_array($v) ? [$k, ...$v] : [$k, $v],
                    array_keys($conditions),
                    array_values($conditions)
                );
            }

            // 处理多维条件
            $this->data = array_filter($this->data, function ($item) use ($conditions) {
                foreach ($conditions as $condition) {
                    if (! $this->compareCondition($item, $condition)) {
                        return false;
                    }
                }

                return true;
            });

            return $this;
        }

        // 基础形式处理
        $this->data = array_filter($this->data, fn ($item) => $this->compareCondition($item, $args));

        return $this;
    }

    /**
     * NULL条件查询
     *
     * @param  string  $field  要查询的字段名
     * @return $this
     *
     * 使用示例:
     * $tree->whereNull('deleted_at');  // 查询deleted_at为NULL的记录
     */
    public function whereNull(string $field): self
    {
        $this->data = array_filter($this->data,
            fn ($item) => ! array_key_exists($field, $item) || $item[$field] === null
        );

        return $this;
    }

    /**
     * 非NULL条件查询
     *
     * @param  string  $field  要查询的字段名
     * @return $this
     *
     * 使用示例:
     * $tree->whereNotNull('email');  // 查询email不为NULL的记录
     */
    public function whereNotNull(string $field): self
    {
        $this->data = array_filter($this->data,
            fn ($item) => array_key_exists($field, $item) && $item[$field] !== null
        );

        return $this;
    }

    /**
     * IN条件查询
     *
     * @param  string  $field  要查询的字段名
     * @param  array  $values  允许的值数组
     * @return $this
     *
     * 使用示例:
     * $tree->whereIn('status', [1, 2, 3]);  // 查询status为1,2或3的记录
     */
    public function whereIn(string $field, array $values): self
    {
        $this->data = array_filter($this->data,
            fn ($item) => array_key_exists($field, $item) && in_array($item[$field], $values, true)
        );

        return $this;
    }

    /**
     * NOT IN条件查询
     *
     * @param  string  $field  要查询的字段名
     * @param  array  $values  排除的值数组
     * @return $this
     *
     * 使用示例:
     * $tree->whereNotIn('role', ['guest', 'test']);  // 查询role不是guest或test的记录
     */
    public function whereNotIn(string $field, array $values): self
    {
        $this->data = array_filter($this->data,
            fn ($item) => array_key_exists($field, $item) && ! in_array($item[$field], $values, true)
        );

        return $this;
    }

    /**
     * BETWEEN条件查询
     *
     * @param  string  $field  要查询的字段名
     * @param  array  $range  范围数组[min, max]
     * @return $this
     *
     * @throws \InvalidArgumentException 当范围参数无效时抛出异常
     *
     * 使用示例:
     * $tree->whereBetween('age', [18, 30]);  // 查询age在18到30之间的记录
     */
    public function whereBetween(string $field, array $range): self
    {
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('BETWEEN条件需要包含最小值和最大值的数组');
        }

        $this->data = array_filter($this->data,
            fn ($item) => array_key_exists($field, $item) &&
                         $item[$field] >= $range[0] &&
                         $item[$field] <= $range[1]
        );

        return $this;
    }

    /**
     * NOT BETWEEN条件查询
     *
     * @param  string  $field  要查询的字段名
     * @param  array  $range  范围数组[min, max]
     * @return $this
     *
     * @throws \InvalidArgumentException 当范围参数无效时抛出异常
     *
     * 使用示例:
     * $tree->whereNotBetween('score', [60, 80]);  // 查询score不在60到80之间的记录
     */
    public function whereNotBetween(string $field, array $range): self
    {
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('NOT BETWEEN条件需要包含最小值和最大值的数组');
        }

        $this->data = array_filter($this->data,
            fn ($item) => array_key_exists($field, $item) &&
                         ($item[$field] < $range[0] || $item[$field] > $range[1])
        );

        return $this;
    }

    /**
     * LIKE条件查询
     *
     * @param  string  $field  要查询的字段名
     * @param  string  $pattern  匹配模式(可以使用%通配符)
     * @param  bool  $caseSensitive  是否区分大小写，默认false
     * @return $this
     *
     * 使用示例:
     * $tree->whereLike('name', '%张%');       // 查询name包含"张"的记录
     * $tree->whereLike('email', '%@gmail.%'); // 查询gmail邮箱
     */
    public function whereLike(string $field, string $pattern, bool $caseSensitive = false): self
    {
        $pattern = str_replace('%', '', $pattern);
        if ($caseSensitive) {
            $this->data = array_filter($this->data,
                fn ($item) => array_key_exists($field, $item) &&
                             str_contains((string) $item[$field], $pattern)
            );
        } else {
            $this->data = array_filter($this->data,
                fn ($item) => array_key_exists($field, $item) &&
                             str_contains(mb_strtolower((string) $item[$field]), mb_strtolower($pattern))
            );
        }

        return $this;
    }

    /**
     * 比较条件核心方法
     *
     * @param  array  $item  当前数据项
     * @param  mixed  $condition  条件参数
     * @return bool 返回是否匹配
     *
     * @throws \InvalidArgumentException 当操作符不支持时抛出异常
     */
    private function compareCondition(array $item, mixed $condition): bool
    {
        // 标准化条件格式
        if (! is_array($condition)) {
            $condition = [$condition];
        }

        $count = count($condition);
        $field = $condition[0] ?? null;
        $operator = $count > 2 ? $condition[1] : '=';
        $value = $count > 2 ? $condition[2] : ($count > 1 ? $condition[1] : null);

        // 检查字段是否存在
        if (! array_key_exists($field, $item)) {
            return false;
        }

        $fieldValue = $item[$field];

        return match ($operator) {
            // 等于比较
            '=', '==' => $fieldValue == $value,
            '===', 'eq' => $fieldValue === $value,

            // 不等于比较
            '!=', '<>' => $fieldValue != $value,
            '!==', 'neq' => $fieldValue !== $value,

            // 数值比较
            '>', 'gt' => $fieldValue > $value,
            '>=', 'gte' => $fieldValue >= $value,
            '<', 'lt' => $fieldValue < $value,
            '<=', 'lte' => $fieldValue <= $value,

            // 字符串比较
            'like' => str_contains((string) $fieldValue, str_replace('%', '', (string) $value)),
            'not like' => ! str_contains((string) $fieldValue, str_replace('%', '', (string) $value)),

            // 包含比较
            'contains' => is_array($fieldValue)
                ? in_array($value, $fieldValue, true)
                : str_contains((string) $fieldValue, (string) $value),
            'not contains' => is_array($fieldValue)
                ? ! in_array($value, $fieldValue, true)
                : ! str_contains((string) $fieldValue, (string) $value),

            // 开头/结尾比较
            'starts with' => str_starts_with((string) $fieldValue, (string) $value),
            'ends with' => str_ends_with((string) $fieldValue, (string) $value),

            // 默认抛出异常
            default => throw new \InvalidArgumentException("不支持的操作符: {$operator}"),
        };
    }

    /**********************************************
     *              条件查询方法组 结束                *
     **********************************************/

    /**
     * 获取原始数据(副本)
     *
     * @return array 原始数据数组的副本
     *
     * 使用示例:
     * $rawData = $tree->getData();
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取第一个匹配的节点
     *
     * @param  mixed  ...$args  同where方法的参数
     * @return array|null 返回第一个匹配的节点或null
     *
     * 使用示例:
     * $node = $tree->firstWhere('id', 5);  // 获取id=5的第一个节点
     */
    public function firstWhere(...$args): ?array
    {
        $clone = clone $this;
        $filtered = $clone->where(...$args)->getData();

        $firstKey = array_key_first($filtered);
        return $filtered[$firstKey] ?? null;
    }

    /**********************************************
     *              修改数据字段方法组开始           *
     **********************************************/
    /**
     * 为满足条件的节点添加/更新字段属性
     *
     * @param  array  $where  条件参数，格式同where方法
     * @param  callable  $attrCallback  返回要添加的属性的回调函数
     * @return $this
     *
     * 使用示例:
     * $tree->addField(['gender' => 1], function() {
     *     return ['is_man' => 1, 'gender_text' => '男性'];
     * });
     */
    public function addField(array $where, callable $attrCallback): self
    {
        // 获取要添加的属性
        $attributes = $attrCallback();
        if (! is_array($attributes) || empty($attributes)) {
            return $this;
        }

        // 筛选符合条件的节点
        $filteredKeys = [];
        $clone = $this->clone();
        $filteredData = $clone->where($where)->getData();

        // 记录符合条件的节点键
        foreach ($filteredData as $item) {
            $filteredKeys[$item[$this->id]] = true;
        }

        // 添加属性
        foreach ($this->data as &$item) {
            if (in_array($item[$this->id], array_keys($filteredKeys))) {
                $item = array_merge($item, $attributes);
            }
        }

        return $this;
    }

    /**
     * 为满足条件的[第一个]节点及其所有父节点 添加/更新字段属性
     *
     * @param  array  $where  条件参数，格式同where方法
     * @param  callable  $attrCallback  返回要添加的属性的回调函数
     * @return $this
     *
     * 使用示例:
     * $tree->addFieldWithParentIds(['gender' => 1], function() {
     *     return ['is_man' => 1, 'gender_text' => '男性'];
     * });
     */
    public function addFieldWithParentIds(array $where, callable $attrCallback): self
    {
        // 获取要添加的属性
        $attributes = $attrCallback();
        if (! is_array($attributes) || empty($attributes)) {
            return $this;
        }

        // 筛选符合条件的节点
        $filteredKeys = [];
        $clone = $this->clone();
        $firstData = $clone->firstWhere($where);
        if (! empty($firstData)) {
            $filteredKeyIds = $clone->getParentAndMeIds($firstData[$this->id]);
            // 记录符合条件的节点键
            foreach ($filteredKeyIds as $id) {
                $filteredKeys[$id] = true;
            }
        }
        // 添加属性
        foreach ($this->data as &$item) {
            if (in_array($item[$this->id], array_keys($filteredKeys))) {
                $item = array_merge($item, $attributes);
            }
        }
        return $this;
    }

    /**
     * 为满足条件的节点移除字段属性
     *
     * @param  array  $where  条件参数，格式同where方法
     * @param  callable  $attrCallback  返回要移除的属性名的回调函数
     * @return $this
     *
     * 使用示例:
     * $tree->removeField(['gender' => 1], function() {
     *     return 'is_man';
     *     // 或者返回数组 ['is_man', 'gender_text']
     * });
     */
    public function removeField(array $where, callable $attrCallback): self
    {
        // 获取要移除的属性名
        $attributes = $attrCallback();
        if (empty($attributes)) {
            return $this;
        }

        // 统一转换为数组
        $attributes = is_array($attributes) ? $attributes : [$attributes];

        // 筛选符合条件的节点
        $filteredKeys = [];
        $clone = $this->clone();
        $filteredData = $clone->where($where)->getData();

        // 记录符合条件的节点键
        foreach ($filteredData as $item) {
            $filteredKeys[$item[$this->id]] = true;
        }

        // 移除属性
        foreach ($this->data as &$item) {
            if (in_array($item[$this->id], array_keys($filteredKeys))) {
                foreach ($attributes as $attr) {
                    unset($item[$attr]);
                }
            }
        }

        return $this;
    }

    /**
     * 为满足条件的第一个节点 及其所有子节点 移除字段属性
     *
     * @param  array  $where  条件参数，格式同where方法
     * @param  callable  $attrCallback  返回要移除的属性名的回调函数
     * @return $this
     *
     * 使用示例:
     * $tree->removeFieldWithParentIds(['gender' => 1], function() {
     *     return 'is_man';
     *     // 或者返回数组 ['is_man', 'gender_text']
     * });
     */
    public function removeFieldWithParentIds(array $where, callable $attrCallback): self
    {
        // 获取要移除的属性名
        $attributes = $attrCallback();
        if (empty($attributes)) {
            return $this;
        }

        // 统一转换为数组
        $attributes = is_array($attributes) ? $attributes : [$attributes];

        // 筛选符合条件的节点
        $filteredKeys = [];
        $clone = $this->clone();
        $firstData = $clone->firstWhere($where);
        if (! empty($firstData)) {
            $filteredKeyIds = $clone->getChildrenAndMeIds($firstData[$this->id]);
            // 记录符合条件的节点键
            foreach ($filteredKeyIds as $id) {
                $filteredKeys[$id] = true;
            }
        }

        // 移除属性
        foreach ($this->data as &$item) {
            if (in_array($item[$this->id], array_keys($filteredKeys))) {
                foreach ($attributes as $attr) {
                    unset($item[$attr]);
                }
            }
        }

        return $this;
    }
    /**********************************************
    *              修改数据字段方法组结束           *
    **********************************************/

    /**
     * 获取所有数据的ID值
     */
    public function getAllIds(): array
    {
        return array_column($this->data, $this->id);
    }

    /**
     * 克隆当前Tree实例（PHP 8风格）
     */
    public function clone(): self
    {
        $clone = new self($this->data);
        $clone->id = $this->id;
        $clone->pid = $this->pid;
        $clone->weight = $this->weight;
        $clone->sortType = $this->sortType;
        $clone->childlist = $this->childlist;
        $clone->rootId = $this->rootId;

        return $clone;
    }

    /**
     * 获取字段值集合
     *
     * @param  string  $field  字段名
     * @param  bool  $unique  是否去重，默认true
     * @return array 字段值数组
     *
     * 使用示例:
     * $ages = $tree->pluck('age');  // 获取所有age值
     */
    public function pluck(string $field, bool $unique = true): array
    {
        $values = array_column($this->data, $field);

        return $unique ? array_unique($values) : $values;
    }

    /**
     * 将树形结构扁平化
     *
     * @param  array  $tree  树形结构数据
     * @param  int  $level  当前层级(内部使用)
     * @return array 扁平化后的数组
     *
     * 使用示例:
     * $treeData = $tree->toTree();
     * $flattened = $tree->flatten($treeData);
     */
    public function flatten(array $tree, int $level = 1): array
    {
        $result = [];
        foreach ($tree as $node) {
            $node['_level'] = $level;
            $result[] = $node;
            if (! empty($node[$this->childlist])) {
                $result = array_merge($result, $this->flatten($node[$this->childlist], $level + 1));
            }
        }

        return $result;
    }

    /**
     * 重置所有属性并清空数据
     *
     * @return $this
     */
    public function reset()
    {
        $this->id = 'id';
        $this->pid = 'pid';
        $this->weight = 'weight';
        $this->sortType = 'desc';
        $this->rootId = 0;
        $this->childlist = 'children';
        // $this->data      = [];

        return $this;
    }

    /**
     * 添加新节点
     *
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
     *
     * @return $this
     */
    public function removeNode(int $id)
    {
        $this->data = array_filter($this->data, fn ($item) => $item[$this->id] !== $id);

        return $this;
    }

    /**
     * 通过主键id更新节点
     *
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
