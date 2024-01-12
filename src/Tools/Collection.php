<?php

namespace zxf\Tools;
/**
 * 集合操作
 */
class Collection
{
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    // 添加一个元素到集合中
    public function add($item)
    {
        $this->items[] = $item;
        return $this;
    }

    // 添加多个元素到集合中
    public function addMultiple(...$items)
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }
        return $this;
    }

    // 根据键名获取集合中指定键名的值
    public function get($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    // 根据键名删除集合中指定键名的项
    public function remove($key)
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
        return $this;
    }

    // 根据给定的回调函数过滤集合中的项，返回新的集合
    public function filter(callable $callback)
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            if ($callback($item)) {
                $result[$key] = $item;
            }
        }
        return new static($result);
    }

    // 根据给定的回调函数对集合中的项进行排序，返回排序后的新集合
    public function sort(callable $callback)
    {
        usort($this->items, $callback);
        return $this;
    }

    // 将集合中的项根据指定的回调函数进行排序，以第一个元素为优先排序依据，返回排序后的新集合
    public function sortByOne(callable $callback)
    {
        return $this->sort(function ($a, $b) use ($callback) {
            return $callback($a[0]) - $callback($b[0]);
        });
    }

    // 将集合中的项根据指定的回调函数进行排序，以第二个元素为优先排序依据，返回排序后的新集合
    public function sortByTwo(callable $callback)
    {
        return $this->sort(function ($a, $b) use ($callback) {
            return $callback($a[1]) - $callback($b[1]);
        });
    }

    // 将集合中的项根据指定的回调函数进行排序，以第三个元素为优先排序依据，返回排序后的新集合
    public function sortByThree(callable $callback)
    {
        return $this->sort(function ($a, $b) use ($callback) {
            return $callback($a[2]) - $callback($b[2]);
        });
    }

    // 将集合中的项进行去重，返回去重后的新集合
    public function unique()
    {
        return new static(array_unique($this->items));
    }

    // 将集合中的项以逗号进行连接，并返回连接后的字符串
    public function implode($glue)
    {
        return implode($glue, $this->items);
    }

    // 将集合中的项转换成字符串，并返回字符串
    public function toString()
    {
        return implode('', $this->items);
    }

    // 将集合中的项转换成数组，并返回数组
    public function toArray()
    {
        return $this->items;
    }

    // 获取集合中的元素数量，并返回数量
    public function count()
    {
        return count($this->items);
    }

    // 判断集合是否为空，如果为空返回true，否则返回false
    public function isEmpty()
    {
        return empty($this->items);
    }

    // 获取集合中所有键名，并返回键名的数组
    public function keys()
    {
        return array_keys($this->items);
    }

    // 获取集合中所有键值，并返回键值的数组
    public function values()
    {
        return array_values($this->items);
    }

    // 将集合中的项与另一个集合中的项进行合并，并返回合并后的集合
    public function merge(Collection $another)
    {
        return new static(array_merge($this->items, $another->items));
    }

    // 将集合中的项进行随机打乱，并返回打乱后的集合
    public function shuffle()
    {
        $shuffled = $this->items;
        shuffle($shuffled);
        return new static($shuffled);
    }

    // 将集合中的项转换成JSON字符串，并返回字符串
    public function toJson()
    {
        return json_encode($this->items);
    }

    // 从JSON字符串转换集合项，并返回集合
    public static function fromJson(string $json)
    {
        return new static(json_decode($json, true));
    }

    // 获取集合中的元素在数组中的键名，并返回键名的数组
    public function indexOf($value)
    {
        return array_search($value, $this->items);
    }

    // 获取集合中的最后一个元素
    public function last()
    {
        return end($this->items);
    }

    // 获取集合中的第一个元素
    public function first()
    {
        return reset($this->items);
    }

    // 将集合中的项按照指定规则进行排序，并返回排序后的集合
    public function sortBy($callback)
    {
        usort($this->items, function ($a, $b) use ($callback) {
            return $callback($a) - $callback($b);
        });
        return $this;
    }

    // 将集合中的项按照指定规则进行排序，并返回排序后的集合
    public function sortByDescending()
    {
        $this->items = array_reverse($this->items);
        return $this;
    }

    // 将集合中的项按照指定规则进行分组，并返回分组后的集合
    public function groupBy($callback)
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $key = $callback($item);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $item;
        }
        return new static($grouped);
    }

    // 获取集合中的最小值，并返回该值
    public function min($callback = null)
    {
        return min(array_map($callback, $this->items));
    }

    // 获取集合中的最大值，并返回该值
    public function max($callback = null)
    {
        return max(array_map($callback, $this->items));
    }

    // 获取集合中的平均值，并返回该值
    public function average($callback = null)
    {
        return array_sum(array_map($callback, $this->items)) / count($this->items);
    }

    // 获取集合中的总和，并返回该值
    public function sum($callback = null)
    {
        return array_sum(array_map($callback, $this->items));
    }

    // 获取集合中的方差，并返回该值
    public function variance($callback = null)
    {
        $avg      = $this->average($callback);
        $variance = array_sum(array_map(function ($value) use ($avg) {
                return pow($value - $avg, 2);
            }, $this->items)) / (count($this->items) - 1);
        return $variance;
    }

    // 获取集合中的标准差，并返回该值
    public function standardDeviation($callback = null)
    {
        $variance = $this->variance($callback);
        return sqrt($variance);
    }

    // 获取集合中的中位数，并返回该值
    public function median($callback = null)
    {
        $sorted = $this->items;
        sort($sorted);
        $count = count($sorted);
        if ($count % 2 == 0) {
            $middle = intval($count / 2);
            return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
        } else {
            $middle = intval($count / 2);
            return $sorted[$middle];
        }
    }

    // 获取集合中的众数，并返回该值
    public function mode($callback = null)
    {
        $counted = array_count_values($this->items);
        arsort($counted);
        foreach ($counted as $item => $count) {
            if ($count > (count($this->items) / 2)) {
                return $item;
            }
        }
        return null;
    }

    // 获取集合中的元素个数，并返回该值
    public function length()
    {
        return count($this->items);
    }

    // 检查集合是否包含指定值，返回bool值
    public function contains($value)
    {
        return in_array($value, $this->items);
    }

    // 检查集合是否包含所有指定值，返回bool值
    public function containsAll(array $values)
    {
        foreach ($values as $value) {
            if (!$this->contains($value)) {
                return false;
            }
        }
        return true;
    }

    // 检查集合是否不包含指定值，返回bool值
    public function notContains($value)
    {
        return !$this->contains($value);
    }

    // 检查集合是否不包含所有指定值，返回bool值
    public function notContainsAll(array $values)
    {
        foreach ($values as $value) {
            if ($this->contains($value)) {
                return false;
            }
        }
        return true;
    }

    // 将集合与另一个集合进行交集运算，并返回结果集合
    public function intersection(Collection $another)
    {
        return new static(array_intersect($this->items, $another->items));
    }

    // 将集合与另一个集合进行并集运算，并返回结果集合
    public function union(Collection $another)
    {
        return new static(array_merge($this->items, $another->items));
    }

    // 将集合与另一个集合进行差集运算，并返回结果集合
    public function difference(Collection $another)
    {
        return new static(array_diff($this->items, $another->items));
    }

    // 将集合与另一个集合进行对称差集运算，并返回结果集合
    public function symmetricDifference(Collection $another)
    {
        return new static(array_diff(array_merge($this->items, $another->items), array_intersect($this->items, $another->items)));
    }

    // 将集合中的项进行旋转，并返回旋转后的集合
    public function rotate($step = 1)
    {
        $count   = count($this->items);
        $step    = intval($step);
        $offset  = ($count + $step) % $count;
        $rotated = array_slice($this->items, $offset, null, true);
        array_unshift($rotated, ...array_splice($this->items, 0, $offset));
        return new static($rotated);
    }

    // 将集合中的项进行反转，并返回反转后的集合
    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    // 将集合中的项按照字符串的字典顺序进行排序，并返回排序后的集合
    public function sortString()
    {
        $this->items = array_merge(...array_chunk($this->items, 1));
        sort($this->items);
        return $this;
    }

    // 将集合中的项按照数值的大小进行排序，并返回排序后的集合
    public function sortNumber()
    {
        sort($this->items, SORT_NUMERIC);
        return $this;
    }

    // 将集合中的项按照指定的回调函数进行排序，并返回排序后的集合
    public function sortByCallback($callback)
    {
        usort($this->items, $callback);
        return $this;
    }

    // 将集合中的项按照指定的回调函数进行逆序排序，并返回排序后的集合
    public function sortByCallbackReversed($callback)
    {
        usort($this->items, function ($a, $b) use ($callback) {
            return $callback($b) - $callback($a);
        });
        return $this;
    }

    // 将集合中的项按照字符串的长度进行排序，并返回排序后的集合
    public function sortByLength()
    {
        usort($this->items, function ($a, $b) {
            return strlen($a) - strlen($b);
        });
        return $this;
    }

    // 将集合中的项按照指定的键名进行排序，并返回排序后的集合
    public function sortByField($fieldName)
    {
        usort($this->items, function ($a, $b) use ($fieldName) {
            return $a[$fieldName] - $b[$fieldName];
        });
        return $this;
    }

    // 将集合中的项按照指定的键名进行逆序排序，并返回排序后的集合
    public function sortByFieldReversed($fieldName)
    {
        usort($this->items, function ($a, $b) use ($fieldName) {
            return $b[$fieldName] - $a[$fieldName];
        });
        return $this;
    }

    // 从集合中随机选择指定数量的项，并返回选择的项组成的集合
    public function sample($size = null)
    {
        if ($size === null) {
            return $this->items[array_rand($this->items)];
        } else {
            return array_slice($this->items, array_rand($this->items, $size), null, true);
        }
    }

    // 从集合中随机选择一个项，并返回该项
    public function getRandom()
    {
        return $this->sample();
    }

    // 将集合中的项按照指定的键名进行分组，并返回分组的集合
    public function groupByField($fieldName)
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $key = $item[$fieldName];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $item;
        }
        return new static($grouped);
    }

    // 将集合中的项按照指定的键名进行分组，并返回分组的集合（不包含指定的键名）
    public function groupByFieldWithoutKey($fieldName)
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $key = $item[$fieldName];
            unset($item[$fieldName]);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $item;
        }
        return new static($grouped);
    }

    // 将集合中的项按照指定的键名进行分组，并返回分组的集合（包含指定的键名）
    public function groupByFieldWithKey($fieldName)
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $key = $item[$fieldName];
            unset($item[$fieldName]);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $item;
        }
        return new static([$fieldName => $grouped]);
    }

    // 将集合中的项按照指定的键名进行分割，并返回分割后的集合
    public function chunkByField($fieldName, $chunkSize = 1000)
    {
        $chunks = [];
        $chunk  = [];
        foreach ($this->items as $item) {
            $key = $item[$fieldName];
            if (!isset($chunks[$key])) {
                $chunks[$key] = [];
            }
            $chunks[$key][] = $item;
            if (count($chunk) >= $chunkSize) {
                $chunks[] = new static($chunk);
                $chunk    = [];
            }
        }
        if (!empty($chunk)) {
            $chunks[] = new static($chunk);
        }
        return new static($chunks);
    }

    // 将集合中的项按照指定的键名进行分割，并返回分割后的集合（不包含指定的键名）
    public function chunkByFieldWithoutKey($fieldName, $chunkSize = 1000)
    {
        $chunks = [];
        $chunk  = [];
        foreach ($this->items as $item) {
            $key = $item[$fieldName];
            unset($item[$fieldName]);
            if (!isset($chunks[$key])) {
                $chunks[$key] = [];
            }
            $chunks[$key][] = $item;
            if (count($chunk) >= $chunkSize) {
                $chunks[] = new static($chunk);
                $chunk    = [];
            }
        }
        if (!empty($chunk)) {
            $chunks[] = new static($chunk);
        }
        return new static([$fieldName => $chunks]);
    }
}