<?php


// 使用php8实现一个面向对象的把多维数组转换为Tree树形结构的高级封装类，要求支持无限自级，要求能自定义id、pid、childlist字段的名称，要求如果有weight字段则根据权重字段weight进行降序排序，要求支持多个根节点，要求能查找出所有后代节点列表和列表树、所有父类节点列表和列表树、根节点,能重置所有参数，能动态添加、删除、修改节点，要求每行都加上中文注释并,要求加上使用示例



class Tree {
    private array $data; // 原始数据数组
    private string $idField = 'id'; // 节点ID字段名，默认为'id'
    private string $pidField = 'pid'; // 父节点ID字段名，默认为'pid'
    private string $childlistField = 'children'; // 子节点列表字段名，默认为'children'

    /**
     * 构造函数，接受原始数据数组
     *
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * 设置节点ID字段名
     *
     * @param string $fieldName
     */
    public function setIdField(string $fieldName): void {
        $this->idField = $fieldName;
    }

    /**
     * 设置父节点ID字段名
     *
     * @param string $fieldName
     */
    public function setPidField(string $fieldName): void {
        $this->pidField = $fieldName;
    }

    /**
     * 设置子节点列表字段名
     *
     * @param string $fieldName
     */
    public function setChildlistField(string $fieldName): void {
        $this->childlistField = $fieldName;
    }

    /**
     * 将原始数据转换为树形结构
     *
     * @return array
     */
    public function convertToTree(): array {
        $treeMap = []; // 使用映射表存储节点信息

        foreach ($this->data as &$item) {
            $itemId = $item[$this->idField];
            $parentId = $item[$this->pidField];

            if (!isset($treeMap[$itemId])) {
                $treeMap[$itemId] = $item; // 添加新节点到映射表中

                // 如果没有子节点，则删除子节点字段
                if (!isset($item['has_children']) || !$item['has_children']) {
                    unset($treeMap[$itemId][$this->childlistField]);
                }
            } else {
                $treeMap[$itemId] += $item; // 更新已存在节点的其他字段
            }

            if ($parentId !== null && isset($treeMap[$parentId])) {
                $treeMap[$parentId][$this->childlistField][] =& $treeMap[$itemId]; // 将当前节点添加为父节点的子节点
            }
        }

        $rootNodes = array_filter($treeMap, fn($item) => !$item[$this->pidField]); // 找出根节点（父节点为空）

        // 使用传统方法对根节点进行排序
        usort($rootNodes, function($a, $b) {
            return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
        });

        $this->sortChildrenByWeight($rootNodes); // 对子节点也按照权重字段进行排序

        return array_values($rootNodes);
    }

    /**
     * 根据权重字段对节点数组进行排序
     *
     * @param array &$nodes 节点数组
     */
    private function sortNodesByWeight(array &$nodes): array {
        uasort($nodes, fn($a, $b) => ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0)); // 根据权重字段对节点进行降序排序
        return $nodes;
    }

    /**
     * 递归地对节点的子节点进行排序
     *
     * @param array &$nodes 节点数组
     */
    private function sortChildrenByWeight(array &$nodes): void {
        foreach ($nodes as &$node) {
            if (isset($node[$this->childlistField])) {
                $children =& $node[$this->childlistField];

                // 使用传统方法对子节点进行排序
                usort($children, function($a, $b) {
                    return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
                });

                $this->sortChildrenByWeight($children); // 递归调用对子节点的子节点进行排序
            }
        }
    }

    private function rootId(): string {
        $roots = array_filter($this->data, fn($item) => empty($item[$this->pidField])); // 找到根节点（父节点为空）
        $root = reset($roots);
        return $root[$this->idField] ?? '';
    }

    public function getDescendants(int $id): array {
        $descendants = [];

        foreach ($this->data as $item) {
            if ($this->isDescendant($item[$this->idField], $id)) {
                $descendants[] = $item[$this->idField];
            }
        }

        return $descendants;
    }

    private function isDescendant(int $itemId, int $parentId): bool {
        foreach ($this->data as $item) {
            if ($item[$this->idField] === $itemId && $item[$this->pidField] === $parentId) {
                return true;
            }

            if ($item[$this->idField] === $itemId && $this->isDescendant($item[$this->pidField], $parentId)) {
                return true;
            }
        }

        return false;
    }

    public function getAncestors(int $id): array {
        $ancestors = [];
        $currentId = $id;

        while ($currentId !== 0) {
            foreach ($this->data as $item) {
                if ($item[$this->idField] === $currentId) {
                    $ancestors[] = $currentId;
                    $currentId = $item[$this->pidField];
                    break;
                }
            }
        }

        return array_reverse($ancestors);
    }

    public function getRoots(): array {
        $roots = [];

        foreach ($this->data as $item) {
            if ($item[$this->pidField] === 0) {
                $roots[] = $item[$this->idField];
            }
        }

        return $roots;
    }

    public function reset(): void {
        $this->idField = 'id';
        $this->pidField = 'pid';
        $this->childlistField = 'children';
    }

    public function addNode(array $node): void {
        $this->data[] = $node;
    }

    public function removeNode(int $id): void {
        $this->data = array_filter($this->data, fn($item) => $item[$this->idField] !== $id);
    }

    public function updateNode(int $id, array $newData): void {
        foreach ($this->data as &$item) {
            if ($item[$this->idField] === $id) {
                $item = array_merge($item, $newData);
                break;
            }
        }
    }
}

// 示例用法
$data = [
    ['id' => 1, 'pid' => 0, 'name' => 'Node 1'],
    ['id' => 2, 'pid' => 0, 'name' => 'Node 2', 'weight' => 10],
    ['id' => 3, 'pid' => 1, 'name' => 'Node 1.1'],
    ['id' => 4, 'pid' => 1, 'name' => 'Node 1.2'],
    ['id' => 5, 'pid' => 3, 'name' => 'Node 1.1.1', 'weight' => 101],
    ['id' => 6, 'pid' => 3, 'name' => 'Node 1.1.2', 'weight' => 1111],
];

$tree = new Tree($data);
$tree->setIdField('id'); // 自定义id字段名
$tree->setPidField('pid'); // 自定义pid字段名
$tree->setChildlistField('children'); // 自定义childlist字段名

$result = $tree->convertToTree();
print_r($result);

$descendants = $tree->getDescendants(1);
print_r($descendants);

// $ancestors = $tree->getAncestors(5);
// print_r($ancestors);

// $roots = $tree->getRoots();
// print_r($roots);

// $tree->reset();

// $newNode = ['id' => 7, 'pid' => 0, 'name' => 'New Node'];
// $tree->addNode($newNode);

// $tree->removeNode(3);

// $updateData = ['name' => 'Updated Node'];
// $tree->updateNode(2, $updateData);

// $result = $tree->convertToTree();
// print_r($result);















