# MySQL 8.x 开窗函数 宏实现


## random
> 解决了 inRandomOrder() 查询随机数 时 SQL 执行特别慢的问题
> 随机查询多少条数据

参数：
```
/**
 * 随机查询宏
 * @param  int  $limit  返回记录数，默认10
 * @param  string  $primaryKey  主键名，默认'id'
 */
```

示例:
```
// 随机选择5名学生
Student::where('class_id', 101)->random(5)->get();
```



## groupRandom 
> 按照指定字段进行分组后从每组中随机取出N条数据

参数:
```
/**
* 分组随机查询宏
*
* @param  string  $groupColumn  分组字段名
* @param  int  $limit  每组返回记录数，默认10
* @param  string  $primaryKey  主键名，默认'id'
*
* @return Builder
*/
```

示例:
```
// 每个班级随机选择2名学生
Student::groupRandom('class_id', 2)->get();;
```

## groupSort
> (分组排序查询) 宏函数

参数:
```
/**
* groupSort(分组排序查询) 宏函数
*
* @param  string  $groupBy  分组字段名 eg: classify_id
* @param  int|array  $ranks  排名，名次(数字表示第n名，带2个数字的数组[n,m]表示查询第n到m名)，倒数第n名使用负数，eg: 1, [2, 4], -1
* @param  string  $orderBy  排序字段名 eg: read
* @param  string  $direction  排序方向，eg: desc, asc
*/
```

示例:
```php
// 查询每个文章分组下read最高的第1到3名文章
Article::query()->groupSort('classify_id', [1,3],'read','desc')->get(); 
// 获取每个文章分组下read第9名文章
Article::query()->groupSort('classify_id', 9,'read','desc')->get(); 
// 获取每个文章分组下read最后1名文章
Article::query()->groupSort('classify_id', -1,'read','desc')->get(); 
```

## 递归查询宏

> 1.withAllChildren : 查找所有子节点
> 2.withAllParents : 查找所有父节点
> 3.withNthParent : 查找第N级父节点
> 4.withNthChildren :查找第N级所有子节点
> 5.withFullPath :查找节点完整路径
> 6.isParentOf :检查是否是父节点
> 7.withSiblings 查找同级节点
> 8.withTree 获取树形结构
> 9.recursiveQuery 通用递归查询
> 10.resetRecursive 重置递归查询

### 1、withAllChildren 查找某个节点的所有子节点

参数:
```php
/**
 * 查找指定节点的所有子节点（递归查找）
 *
 * @param  int $id：起始节点ID
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @param  int $maxDepth = 100：最大递归深度
 * @return Illuminate\Database\Eloquent\Builder
 */
```

示例:
```php
// 查找ID为5的节点的所有子节点
$children = ArticleClassify::withAllChildren(5)
    ->where('status', 1)
    ->orderBy('sort', 'desc')
    ->get();

// 自定义父ID字段名和最大深度
$children = ArticleClassify::withAllChildren(5, 'parent_id', 10)
    ->get();
```

### 2、withAllParents 查找所有父节点

参数:
```php
/**
 * 查找指定节点的所有父节点（递归查找）
 *
 * @param  int $id：起始节点ID
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @param  int $maxDepth = 100：最大递归深度
 * @return Builder
 */
```

示例:
```php
// 查找ID为8的节点的所有父节点
$parents = ArticleClassify::withAllParents(8)
    ->where('show_nav', 1)
    ->get();

// 自定义父ID字段名
$parents = ArticleClassify::withAllParents(8, 'parent_id')
    ->get();
```

### 3、withNthParent 查找第N级父节点

参数:
```php
/**
 * 查找指定节点的第N级父节点（自己算0级）
 *
 * @param  int $id：起始节点ID
 * @param  int $n：要查找的父级层级
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @return Builder
 */
```

示例:
```php
// 查找ID为10的节点的第2级父节点
$parent = ArticleClassify::withNthParent(10, 2)
    ->first();

// 查找第3级父节点，自定义父ID字段名
$parent = ArticleClassify::withNthParent(10, 3, 'parent_id')
    ->first();
```

### 4、withNthChildren 查找第N级所有子节点

参数:
```php
/**
 * 查找指定节点的第N级所有子节点（自己算0级）
 *
 * @param  int $id：起始节点ID
 * @param  int $n：要查找的子级层级
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @return Builder
 */
```

示例:
```php
// 查找ID为1的节点的第2级所有子节点
$children = ArticleClassify::withNthChildren(1, 2)
    ->where('type', 1)
    ->get();

// 查找第3级子节点，自定义父ID字段名
$children = ArticleClassify::withNthChildren(1, 3, 'parent_id')
    ->get();
```

### 5、withFullPath 查找节点完整路径

参数:
```php
/**
 * 查找节点的完整路径（包含absolute_path字段）
 *
 * @param  array $ids = []：要查询的ID数组（为空则查询所有）
 * @param  array $conditions = []：额外查询条件
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @param  string $nameColumn = 'name'：名称字段名
 * @param  string $pathSeparator = ' > '：路径分隔符
 * @return Builder
 */
```

示例:
```php
// 查找ID为1,2,3的节点的完整路径
$paths = ArticleClassify::withFullPath([1, 2, 3])
    ->get();

// 带条件查询，自定义分隔符
$paths = ArticleClassify::withFullPath(
    [1, 2, 3], 
    ['status' => 1], 
    'pid', 
    'name', 
    ' / '
)->get();
```

### 6、isParentOf 检查是否是父节点

参数:
```php
/**
 * 检查第一个节点是否是第二个节点的父节点
 *
 * @param  int $parentId：可能的父节点ID
 * @param  int $childId：子节点ID
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @return bool
 */
```

示例:
```php
// 检查ID为1的节点是否是ID为5的节点的父节点
$isParent = ArticleClassify::isParentOf(1, 5);

// 自定义父ID字段名
$isParent = ArticleClassify::isParentOf(1, 5, 'parent_id');
```

### 7、withSiblings 查找同级节点

参数:
```php
/**
 * 查找某个节点的所有同级节点
 *
 * @param  int $id：节点ID
 * @param  string $pidColumn = 'pid'：父ID字段名
 * @param  bool $includeSelf = false：是否包含自己
 * @return Builder
 */
```

示例:
```php
// 查找ID为5的节点的所有同级节点（不包含自己）
$siblings = ArticleClassify::withSiblings(5)
    ->where('status', 1)
    ->get();

// 查找同级节点（包含自己），自定义父ID字段名
$siblings = ArticleClassify::withSiblings(5, 'parent_id', true)
    ->get();
```

### 8、withTree 获取树形结构

参数:
```php
/**
 * 查找从指定节点开始的树形结构数据
 *
 * @param  ?int $pid = null：起始父ID（null表示从根节点开始）
 * @param string $pidColumn = 'pid'：父ID字段名
 * @param string $nameColumn = 'name'：名称字段名
 * @param int $maxDepth = 100：最大深度
 * @param string $pathSeparator = ' > '：路径分隔符
 * @return Builder
 */
```

示例:
```php
// 获取完整的树形结构
$tree = ArticleClassify::withTree()
    ->where('status', 1)
    ->get();

// 从指定节点开始的子树，自定义参数
$subTree = ArticleClassify::withTree(
    5, 
    'parent_id', 
    'title', 
    5, 
    ' -> '
)->get();
```

### 9、recursiveQuery 通用递归查询

参数:
```php
/**
 * 高级自定义递归查询
 *
 * @param  callable $baseQuery：基础查询回调 function(Builder $query, string $withTable): string
 * @param callable $recursiveQuery：递归查询回调 function(Builder $query, string $withTable): string
 * @param array $columns = ['*']：查询列
 * @param int $maxDepth = 100：最大深度
 * @param string $depthColumn = 'depth'：深度字段名
 * @return Builder
 */
```

示例:
```php
// 自定义递归查询
$customQuery = ArticleClassify::recursiveQuery(
    function ($query, $withTable) {
        /** @var Builder $query */
        $table = $query->getModel()->getTable();
        return "SELECT *, 0 AS depth FROM `{$table}` WHERE `pid` = 1";
    },
    function ($query, $withTable) {
        /** @var Builder $query */
        $table = $query->getModel()->getTable();
        return "SELECT t.*, r.depth + 1 AS depth 
                FROM `{$table}` t
                JOIN `{$withTable}` r ON t.`pid` = r.`id`";
    },
    ['id', 'name', 'pid'],
    5
)->get();

// 自定义深度列名
$customQuery = ArticleClassify::recursiveQuery(
    // ... 回调函数同上 ...,
    ['*'],
    10,
    'level'
)->get();
```

### 10、resetRecursive 重置递归查询（清除所有递归条件）

参数:
```php
/**
 * 重置递归查询（清除所有递归条件）
 * @return Builder
 */
```

示例:
```php
// 先执行递归查询
$query = ArticleClassify::query()
    ->withAllChildren(1);

// 然后重置递归条件，继续普通查询
$normalQuery = $query->resetRecursive()
    ->where('status', 1)
    ->get();
```
