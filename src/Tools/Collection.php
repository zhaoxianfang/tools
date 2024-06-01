<?php

namespace zxf\Tools;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use ArrayIterator;
use Countable;
use JsonSerializable;
use Traversable;

/**
 * 集合操作
 */
class Collection implements ArrayAccess, Countable, JsonSerializable, IteratorAggregate
{
    /**
     * 存储集合数据的私有属性
     *
     * @var array
     */
    private array $items = [];

    /**
     * 构造函数，接受数组或可遍历对象初始化集合
     *
     * @param iterable $items 初始数据
     *
     * @throws InvalidArgumentException 如果输入既不是数组也不是可遍历对象
     */
    public function __construct(iterable $items = [])
    {
        if (is_array($items)) {
            $this->items = $this->convertToCollections($items);
        } elseif ($items instanceof Traversable) {
            $this->items = iterator_to_array($items);
        } else {
            throw new InvalidArgumentException('Collection 类构造函数只接受数组或可遍历对象');
        }
    }

    /**
     * 将多维数组转换为包含Collection对象的结构
     *
     * @param array $items 要转换的数组
     *
     * @return array 转换后的数组，其中子数组被替换为Collection对象
     */
    private function convertToCollections(array $items): array
    {
        $result = [];
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $result[$key] = new static($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 实现 ArrayAccess 接口的 offsetExists 方法
     *
     * @param mixed $offset 偏移量
     *
     * @return bool 是否存在该偏移量
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * 实现 ArrayAccess 接口的 offsetGet 方法
     *
     * @param mixed $offset 偏移量
     *
     * @return mixed 偏移量对应的值
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * 实现 ArrayAccess 接口的 offsetSet 方法
     *
     * @param mixed $offset 偏移量
     * @param mixed $value  对应的值
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * 实现 ArrayAccess 接口的 offsetUnset 方法
     *
     * @param mixed $offset 偏移量
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * 实现 Countable 接口的 count 方法
     *
     * @return int 数组元素的数量
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * 实现 JsonSerializable 接口的 jsonSerialize 方法
     * 使得对象可以直接用于 json_encode
     *
     * @return array 返回用于 json 编码的数组
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 递归地将Collection对象转换为数组
     *
     * @param mixed $items 可能包含Collection对象的项
     *
     * @return array 转换后的数组
     */
    private function recursiveToArray(mixed $items): array
    {
        $array = [];
        foreach ($items as $key => $value) {
            if ($value instanceof Collection) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    // 实现 IteratorAggregate 接口,IteratorAggregate和Iterator接口只能选择一个
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * 将当前集合转换为普通数组
     *
     * @return array 转换后的数组
     */
    public function toArray(): array
    {
        return $this->recursiveToArray($this->items);
    }

    /**
     * 自定义方法，直接返回JSON字符串
     *
     * @return string JSON格式的字符串表示
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /* 实现__toString接口，用于直接输出JSON格式的字符串 */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * 允许通过对象属性方式访问数组元素
     *
     * @param string $property 属性名
     *
     * @return mixed 属性值
     */
    public function __get(string $property)
    {
        if (isset($this->items[$property])) {
            return $this->items[$property];
        }
        return null;
    }

    /**
     * 实现迭代器的current方法，以便在遍历时通过对象属性访问数据
     *
     * @return mixed 当前元素
     */
    public function current(): mixed
    {
        return $this->items[current($this->items)];
    }

    /**
     * 静态方法调用
     *
     * @throws Exception
     */
    public static function __callStatic(string $method, mixed $arg): mixed
    {
        throw new Exception($method . ' 方法不存在');
    }


    // ===================================
    //    一些常用操作方法
    // ===================================

    /**
     * 检查集合是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    /**
     * 过滤集合，根据回调函数返回符合条件的元素组成新的集合
     *
     * @param callable $callback 回调函数，返回true则保留该元素
     *
     * @return static 返回一个新的Collection实例
     */
    public function filter(callable $callback): static
    {
        $filteredItems = array_filter($this->toArray(), $callback, ARRAY_FILTER_USE_BOTH);
        return new static($filteredItems);
    }


    /**
     * 从集合中移除所有null值
     *
     * @return static 返回一个新的无null值的Collection实例
     */
    public function filterNull(): static
    {
        return new static(array_filter($this->toArray(), function ($value) {
            return !empty($value);
        }));
    }

    /**
     * 递归获取对象或数组中的值
     *
     * @param mixed             $target  目标对象或数组
     * @param array|string|null $keyPath 键路径，可以是单个键或键的数组表示路径
     * @param mixed|null        $default 默认值，当路径不存在时返回
     *
     * @return mixed 返回找到的值或默认值
     */
    protected function getData(mixed $target, array|string|null $keyPath, mixed $default = null): mixed
    {
        if (is_null($keyPath)) {
            return $target;
        }

        if (is_array($keyPath)) {
            foreach ($keyPath as $key) {
                if (is_array($target) && array_key_exists($key, $target)) {
                    $target = $target[$key];
                } elseif (is_object($target) && property_exists($target, $key)) {
                    $target = $target->$key;
                } else {
                    return $default;
                }
            }
            return $target;
        } else {
            if (is_array($target) && array_key_exists($keyPath, $target)) {
                return $target[$keyPath];
            } elseif (is_object($target) && property_exists($target, $keyPath)) {
                return $target->$keyPath;
            }
            return $default;
        }
    }

    /**
     * 判断字符串是否匹配like模式
     *
     * @param int|string|null $string  $string  数字或字符串
     * @param string          $pattern 匹配模式字符串，支持%符, 例如：
     *                                 'A%'：表示以A开头的字符串，
     *                                 'A%B'：表示以A开头并以B结尾的字符串，
     *                                 '%B'：表示以B结尾的字符串，
     *                                 '%A%'：表示以包含A的字符串，
     *                                 'ABC'：表示以匹配ABC字符串，
     *
     * @return bool
     */
    protected function stringLike(int|string|null $string, string $pattern): bool
    {
        if (empty($string)) {
            return false;
        }
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool)preg_match("/^$pattern$/", $string);
    }

    /**
     * 基于条件筛选集合中的元素
     *      eg:where('id', 3)
     *          where('id','>', 3)
     *          where('name', 'like', 'A%')
     *          where('name', 'like', '%A%')
     *          where([
     *              ['id', '>', 1],
     *              ['name', '=', 'Bob']
     *          ])
     *
     * @param callable|array|string $columnOrCallback 查询的键、闭包或条件数组
     * @param mixed|null            $operatorOrValue  操作符或值，对于键查询是操作符，对于闭包查询忽略此参数
     * @param mixed|null            $value            查询的值，对于键查询使用
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    public function where(callable|array|string $columnOrCallback, mixed $operatorOrValue = null, mixed $value = null): static
    {
        if (is_callable($columnOrCallback)) {
            return $this->filter($columnOrCallback);
        } elseif (is_array($columnOrCallback)) {
            return $this->whereNested($columnOrCallback);
        } else {
            $operator = empty($value) ? '=' : $operatorOrValue;
            $value    = $value ?? $operatorOrValue;
            return new static($this->filter(function ($item) use ($columnOrCallback, $operator, $value) {
                $itemValue = $this->getData($item, $columnOrCallback);
                if (is_callable($operator)) {
                    return $operator($itemValue);
                }
                switch ($operator) {
                    case '=':
                    case '==':
                        return $itemValue == $value;
                    case '!=':
                    case '<>':
                        return $itemValue != $value;
                    case '>':
                        return $itemValue > $value;
                    case '<':
                        return $itemValue < $value;
                    case '>=':
                        return $itemValue >= $value;
                    case '<=':
                        return $itemValue <= $value;
                    case 'like':
                    case 'LIKE':
                        return $this->stringLike($itemValue, $value);
                    case 'null':
                        return $itemValue === null;
                    case 'not_null':
                        return $itemValue !== null;
                    default:
                        throw new InvalidArgumentException("Unsupported operator '$operator'.");
                }
            }));

        }
    }

    /**
     * 支持多个条件的嵌套查询
     *
     * @param array $conditions 条件数组
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    protected function whereNested(array $conditions): static
    {
        $filteredItems = $this->items;
        foreach ($conditions as $condition) {
            list($key, $operator, $value) = $condition;
            $filteredItems = $this->where($key, $operator, $value)->toArray();
        }
        return new static($filteredItems);
    }

    /**
     * 查询指定键值存在于给定数组中的元素
     *
     * @param string $column 键名
     * @param array  $values 值数组
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    public function whereIn(string $column, array $values): static
    {
        return $this->where($column, function ($itemValue) use ($values) {
            return in_array($itemValue, $values);
        });
    }

    /**
     * 查询指定键值为空的元素
     *
     * @param string $column 键名
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    public function whereNull(string $column): static
    {
        return $this->where($column, 'null');
    }

    /**
     * 查询指定键值非空的元素
     *
     * @param string $column 键名
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    public function whereNotNull(string $column): static
    {
        return $this->where($column, 'not_null');
    }

    /**
     * 查询指定键值在某个区间内的元素
     *
     * @param string $column 键名
     * @param mixed  $min    最小值
     * @param mixed  $max    最大值
     *
     * @return static 返回符合筛选条件的新集合实例
     */
    public function whereBetween(string $column, $min, $max): static
    {
        return $this->where($column, function ($itemValue) use ($min, $max) {
            return $itemValue >= $min && $itemValue <= $max;
        });
    }

    /**
     * 向集合中添加元素
     *
     * @param mixed $element 要添加的元素，可以是单个值或另一个Collection
     *
     * @return $this 返回当前集合，便于链式调用
     */
    public function add(mixed $element): self
    {
        if (is_array($element)) {
            $this->items = array_merge($this->items, $element);
        } elseif ($element instanceof Collection) {
            $this->items = array_merge($this->items, $element->toArray());
        } else {
            $this->items[] = $element;
        }
        return $this;
    }

    /**
     * 从集合中移除指定的元素
     *
     * @param mixed $element 要移除的元素，可以是值或键
     *
     * @return bool 是否成功移除
     */
    public function remove(mixed $element): bool
    {
        $key = array_search($element, $this->items, true);
        if ($key !== false) {
            unset($this->items[$key]);
            return true;
        }
        return false;
    }

    /**
     * 合并两个集合
     *
     * @param array|Collection $other 另一个集合或数组
     *
     * @return $this 返回当前集合，便于链式调用
     */
    public function merge(array|Collection $other): self
    {
        if ($other instanceof Collection) {
            $other = $other->toArray();
        }
        $this->items = array_merge($this->items, $other);
        return $this;
    }

    /**
     * 对集合进行排序
     *
     * @param callable|null $callback 排序比较函数，默认按自然顺序排序
     *
     * @return $this 返回当前集合，便于链式调用
     */
    public function sort(?callable $callback = null): self
    {
        if ($callback === null) {
            sort($this->items);
        } else {
            usort($this->items, $callback);
        }
        return $this;
    }

    /**
     * 按键对关联数组进行排序
     *
     * @param int $order SORT_ASC 升序，SORT_DESC 降序
     *
     * @return static 返回排序后的Collection实例
     */
    public function sortKeys(int $order = SORT_ASC): static
    {
        if ($this->isAssoc()) {
            ksort($this->items, $order);
        } else {
            sort($this->items, $order);
        }
        return new static($this->items);
    }

    /**
     * 判断数组是否为关联数组
     *
     * @return bool 如果集合代表的是关联数组返回true，否则返回false
     */
    protected function isAssoc(): bool
    {
        if (empty($this->items)) {
            return false;
        }
        return array_keys($this->items) !== range(0, count($this->items) - 1);
    }

    /**
     * 根据指定键或闭包对集合进行降序排序
     *
     * @param callable|int|string $keyOrClosure 排序依据的键名或排序闭包
     *
     * @return static 返回降序排序后的Collection实例
     */
    public function sortByDesc(callable|int|string $keyOrClosure): static
    {
        return $this->sortBy($keyOrClosure, SORT_DESC);
    }

    /**
     * 根据多个键或闭包对集合进行排序
     *
     * @param array $criteria 多个排序条件，每个条件为键名或闭包及其排序顺序
     *
     * @return static 返回排序后的Collection实例
     */
    public function sortByMulti(array $criteria): static
    {
        usort($this->items, function ($a, $b) use ($criteria) {
            foreach ($criteria as $keyOrClosure => $order) {
                $comparison = is_callable($keyOrClosure) ? $keyOrClosure($a, $b) : ($a[$keyOrClosure] <=> $b[$keyOrClosure]);
                if ($comparison !== 0) {
                    return ($order === 'desc' || $order === SORT_DESC) ? -$comparison : $comparison;
                }
            }
            return 0;
        });
        return new static($this->items);
    }

    /**
     * 移除集合中的重复元素
     *
     * @return $this 返回当前集合，便于链式调用
     */
    public function unique(): self
    {
        $this->items = array_unique($this->toArray(), SORT_REGULAR);
        return $this;
    }

    /**
     * 对集合中的每个元素应用自定义函数
     * eg:->map(fn($n) => $n * 2); // 每个元素乘以2
     *
     * @param callable $callback 应用的函数，接受元素作为参数并返回新值
     *
     * @return static 返回一个新的Collection实例
     */
    public function map(callable $callback): static
    {
        $mappedItems = array_map($callback, $this->items, array_keys($this->items));
        return new static($mappedItems);
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
    }

    /**
     * 对集合中的元素应用累积函数
     * eg:->reduce(fn($carry, $item) => $carry + $item, 0) // 计算总和
     *
     * @param callable   $callback 累积函数，接受上一次的结果和当前元素作为参数
     * @param mixed|null $initial  初始值
     *
     * @return mixed 函数累积处理后的结果
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * 从集合中的每个元素提取指定键的值，可选地使用另一键作为新集合的键
     * eg:->pluck('name'); // 提取每个元素的name属性
     * ->pluck('name', 'id'); // 提取每个元素的name属性，作为新集合的键
     *
     * @param string $column 要提取的键名
     * @param string $key    作为新集合键的键名，可选
     *
     * @return static 返回一个新的Collection实例
     */
    public function pluck(string $column, string $key = ''): static
    {
        $result = [];
        foreach ($this->items as $item) {
            if (empty($key)) {
                // 如果$key为空，直接提取$column的值
                $result[] = $item[$column] ?? null;
            } else {
                // 如果$key有值，使用$key的值作为新数组的键，$column的值作为键对应的值
                $result[$item[$key]] = $item[$column] ?? null;
            }
        }
        return new static($result);
    }

    /**
     * 过滤掉满足给定条件的元素
     * eg:->reject(fn($n) => $n % 2 == 0); // 过滤偶数
     *
     * @param callable $callback 条件函数，返回true则元素被过滤
     *
     * @return static 返回一个新的Collection实例，不含满足条件的元素
     */
    public function reject(callable $callback): static
    {
        return $this->filter(fn($value, $key) => !$callback($value, $key));
    }

    /**
     * 将集合分割成多个小集合，每个小集合包含固定数量的元素，并对每个小集合应用回调函数
     * eg: $collection->chunk(3, function ($items, $index) {
     * })
     *
     * @param int      $size     每个小集合的大小
     * @param callable $callback 在处理每个小集合时调用的回调函数
     *
     * @return array 返回经过回调处理后的集合数组
     */
    public function chunk(int $size, callable $callback): array
    {
        $chunks       = [];
        $currentIndex = 0;
        foreach (array_chunk($this->items, $size) as $items) {
            $chunks[] = $callback($items, $currentIndex++);
        }
        return $chunks;
    }

    /**
     * 根据条件连续分块集合
     *
     * @param callable $callback 判断是否开始新块的闭包函数
     *
     * @return static 返回连续分块后的Collection实例，每个元素是一个子集合
     */
    public function chunkBy(callable $callback): static
    {
        $chunks       = [];
        $currentChunk = [];
        foreach ($this->items as $item) {
            if ($callback($item, $currentChunk)) {
                if (!empty($currentChunk)) {
                    $chunks[]     = new static($currentChunk);
                    $currentChunk = [];
                }
            }
            $currentChunk[] = $item;
        }
        if (!empty($currentChunk)) {
            $chunks[] = new static($currentChunk);
        }
        return new static($chunks);
    }

    /**
     * 反转集合中的元素顺序
     *
     * @return $this 返回当前集合，元素顺序已反转
     */
    public function reverse(): self
    {
        $this->items = array_reverse($this->items);
        return $this;
    }

    /**
     * 返回集合中的一部分元素
     *
     * @param int      $offset 开始位置
     * @param int|null $length 可选，长度
     *
     * @return static 返回一个新的Collection实例，包含指定范围的元素
     */
    public function slice(int $offset, ?int $length = null): static
    {
        $slicedItems = array_slice($this->items, $offset, $length);
        return new static($slicedItems);
    }

    /**
     * 返回集合的第一个元素
     *
     * @return mixed 返回第一个元素，如果集合为空则返回null
     */
    public function first(): mixed
    {
        return count($this->items) > 0 ? reset($this->items) : null;
    }

    /**
     * 返回集合的最后一个元素
     *
     * @return mixed 返回最后一个元素，如果集合为空则返回null
     */
    public function last(): mixed
    {
        return count($this->items) > 0 ? end($this->items) : null;
    }

    /**
     * 检查集合是否包含某个值
     *
     * @param mixed $value 要检查的值
     *
     * @return bool 如果集合包含该值则返回true，否则返回false
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * 根据指定键将集合中的元素分组
     *
     * @param int|string $key 分组依据的键
     *
     * @return static 返回一个新的Collection实例，包含分组后的集合
     */
    public function groupBy(int|string $key): static
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $groupKey             = is_object($item) ? $item->$key : $item[$key];
            $grouped[$groupKey][] = $item;
        }
        return new static($grouped);
    }

    /**
     * 将多维集合扁平化为一维
     *
     * @param int $depth 可选，递归深度，默认为INF
     *
     * @return static 返回一个新的Collection实例，包含扁平化的元素
     */
    public function flatten(int $depth = INF): static
    {
        $flattened = [];
        array_walk_recursive($this->items, function ($item) use (&$flattened) {
            $flattened[] = $item;
        }, $depth);
        return new static($flattened);
    }

    /**
     * 计算集合内数值型元素的平均值，针对多维数组中的特定列
     *
     * @param int|string $column 要计算平均值的列名或键
     *
     * @return float|null 返回平均值，如果集合为空或无该列或该列无数值则返回null
     */
    public function avg(int|string $column): ?float
    {
        $values        = array_column($this->items, $column);
        $numericValues = array_filter($values, function ($value) {
            return is_numeric($value);
        });

        if (empty($numericValues)) {
            return null;
        }

        return array_sum($numericValues) / count($numericValues);
    }

    /**
     * 计算集合内数值型元素的总和，针对多维数组中的特定列
     *
     * @param int|string $column 要计算总和的列名或键
     *
     * @return float 返回总和，如果集合为空则返回0
     */
    public function sum(int|string $column): float
    {
        $values        = array_column($this->items, $column);
        $numericValues = array_filter($values, function ($value) {
            return is_numeric($value);
        });

        return array_sum($numericValues);
    }

    /**
     * 找到集合内数值型元素的最大值，针对多维数组中的特定列
     *
     * @param int|string $column 要寻找最大值的列名或键
     *
     * @return float|int|null 返回最大值，如果集合为空或无该列或该列无数值则返回null
     */
    public function max(int|string $column): float|int|null
    {
        $values        = array_column($this->items, $column);
        $numericValues = array_filter($values, function ($value) {
            return is_numeric($value);
        });

        if (empty($numericValues)) {
            return null;
        }

        return max($numericValues);
    }

    /**
     * 找到集合内数值型元素的最小值，针对多维数组中的特定列
     *
     * @param int|string $column 要寻找最小值的列名或键
     *
     * @return float|int|null 返回最小值，如果集合为空或无该列或该列无数值则返回null
     */
    public function min(int|string $column): float|int|null
    {
        $values        = array_column($this->items, $column);
        $numericValues = array_filter($values, function ($value) {
            return is_numeric($value);
        });

        if (empty($numericValues)) {
            return null;
        }

        return min($numericValues);
    }

    /**
     * 根据指定键或闭包对集合进行排序
     *
     * @param callable|int|string $keyOrClosure 排序依据的键名或排序闭包
     * @param int                 $direction    排序方向，SORT_ASC 升序，SORT_DESC 降序，默认升序
     *
     * @return static 返回一个新的排序后的Collection实例
     */
    public function sortBy(callable|int|string $keyOrClosure, int $direction = SORT_ASC): static
    {
        if (is_callable($keyOrClosure)) {
            usort($this->items, $keyOrClosure);
        } else {
            usort($this->items, function ($a, $b) use ($keyOrClosure, $direction) {
                return ($a[$keyOrClosure] <=> $b[$keyOrClosure]) * ($direction == SORT_DESC ? -1 : 1);
            });
        }
        return new static($this->items);
    }

    /**
     * 随机打乱集合中元素的顺序
     *
     * @return static 返回一个新的随机排序后的Collection实例
     */
    public function shuffle(): static
    {
        shuffle($this->items);
        return new static($this->items);
    }

    /**
     * 将多个集合合并为一个集合，每个元素都是由各集合中对应位置的元素组成的数组
     *
     * @param iterable ...$collections 要合并的其他集合
     *
     * @return static 返回一个新的包含合并结果的Collection实例
     */
    public function zip(iterable ...$collections): static
    {
        $zipped = array_map(null, ...array_map(fn($col) => $col instanceof Collection ? $col->toArray() : $col->toArray(), array_merge([$this], $collections)));
        return new static(array_filter($zipped, fn($row) => !in_array(null, $row, true)));
    }

    /**
     * 返回一个新集合，包含此集合中有但目标集合中没有的元素
     *
     * @param array|Collection $target 目标集合
     *
     * @return static 返回一个新的差异集合实例
     */
    public function diff(self|array $target): static
    {
        $targetArray = is_array($target) ? $target : $target->toArray();
        $diffItems   = array_diff_assoc($this->items, $targetArray);
        return new static($diffItems);
    }

    /**
     * 检查集合中的所有元素是否都满足给定条件
     *
     * @param callable $callback 条件闭包
     *
     * @return bool 所有元素都满足条件则返回true，否则返回false
     */
    public function every(callable $callback): bool
    {
        foreach ($this->items as $key => $value) {
            if (!$callback($value, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查集合中是否存在至少一个元素满足给定条件
     *
     * @param callable $callback 条件闭包
     *
     * @return bool 至少有一个元素满足条件则返回true，否则返回false
     */
    public function some(callable $callback): bool
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 将集合的元素连接成字符串
     *
     * @param string $glue 连接符，默认为空格
     *
     * @return string 连接后的字符串
     */
    public function join(string $glue = ' '): string
    {
        return implode($glue, array_map('strval', $this->items));
    }

    /**
     * 根据指定键或闭包对集合元素进行分组计数
     *
     * @param callable|int|string $keyOrClosure 分组依据的键名或闭包
     *
     * @return array 分组计数的结果
     */
    public function countBy(callable|int|string $keyOrClosure): array
    {
        $counts = [];
        foreach ($this->items as $item) {
            $key = is_callable($keyOrClosure) ? $keyOrClosure($item) : $item[$keyOrClosure];
            if (array_key_exists($key, $counts)) {
                $counts[$key]++;
            } else {
                $counts[$key] = 1;
            }
        }
        return $counts;
    }

    /**
     * 返回一个新的集合，排除指定的元素或满足条件的元素
     *
     * @param mixed ...$values 要排除的具体值
     *
     * @return static 返回一个新的排除指定元素后的Collection实例
     */
    public function without(...$values): static
    {
        $callback = null;
        if (count($values) > 0 && is_callable(end($values))) {
            $callback = array_pop($values);
        }

        $filteredItems = array_filter($this->items, function ($item) use ($values, $callback) {
            if ($callback) {
                return !$callback($item);
            }
            return !in_array($item, $values, true);
        });

        return new static($filteredItems);
    }


    /**
     * 返回集合中所有键的集合
     *
     * @return static 返回一个包含所有键的Collection实例
     */
    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    /**
     * 返回集合中所有值的集合
     *
     * @return static 返回一个包含所有值的Collection实例
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * 随机抽取集合中的一个或多个样本
     *
     * @param int|null $numberOfSamples 抽取的样本数量，如果不指定则默认抽取一个样本
     *
     * @return null|static 返回一个或多个样本值，或者一个新的包含样本的Collection实例
     */
    public function sample(int $numberOfSamples = null): null|static
    {
        if ($numberOfSamples === null) {
            $keys = array_keys($this->items);
            return $this->items[array_rand($keys)];
        } else {
            $sampledItems = [];
            $keys         = array_keys($this->items);
            while (count($sampledItems) < $numberOfSamples) {
                $randomKey = array_rand($keys);
                if (!in_array($randomKey, $sampledItems, true)) {
                    $sampledItems[] = $randomKey;
                }
            }
            return new static(array_intersect_key($this->items, array_flip($sampledItems)));
        }
    }

    /**
     * 在集合的开始或结束处填充指定数量的值
     *
     * @param int    $size  填充的长度
     * @param mixed  $value 填充的值
     * @param string $mode  'start' 表示开头填充，'end' 表示末尾填充，默认为'start'
     *
     * @return static 返回一个填充后的Collection实例
     */
    public function pad(int $size, mixed $value, string $mode = 'start'): static
    {
        $padding = array_fill(0, $size, $value);
        if ($mode === 'start') {
            $items = array_merge($padding, $this->items);
        } elseif ($mode === 'end') {
            $items = array_merge($this->items, $padding);
        } else {
            throw new InvalidArgumentException("Invalid mode. Use 'start' or 'end'.");
        }
        return new static($items);
    }

    /**
     * 根据指定的比较函数找出两个集合的交集
     *
     * @param array|Collection $other      另一个集合
     * @param callable         $comparator 比较函数，接受两个元素并返回它们是否相等的布尔值
     *
     * @return static 返回交集的Collection实例
     */
    public function intersectBy(self|array $other, callable $comparator): static
    {
        $otherArray   = is_array($other) ? $other : $other->toArray();
        $intersection = array_uintersect($this->items, $otherArray, $comparator);
        return new static($intersection);
    }

    /**
     * 根据指定的比较函数找出两个集合的差异
     *
     * @param array|Collection $other      另一个集合
     * @param callable         $comparator 比较函数，接受两个元素并返回它们是否相等的布尔值
     *
     * @return static 返回差异的Collection实例
     */
    public function differenceBy(self|array $other, callable $comparator): static
    {
        $otherArray = is_array($other) ? $other : $other->toArray();
        $difference = array_udiff($this->items, $otherArray, $comparator);
        return new static($difference);
    }

    /**
     * 获取数组的 维度/深度
     *
     * @param mixed $data 数组
     *
     * @return int 数组的维度
     */
    public function getArrayDimension(mixed $data): int
    {
        if (empty($data)) {
            $data = $this->toArray();
        }

        return (is_array($data) && !empty($data)) ? (1 + max(array_map([$this, 'getArrayDimension'], $data))) : 0;
    }
}
