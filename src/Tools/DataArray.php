<?php

namespace zxf\Tools;

use ArrayAccess;
use Countable;
use Generator;
use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;

/**
 * 把数组转换为对象调用
 * $options=[
 *      'a'=>'aa',
 *      'b'=>'bb'
 * ];
 * $data = new DataArray($options);
 * 1、直接使用数组的方式调用
 * var_dump($data['a']);
 * 2、使用对象的方式调用
 * var_dump($data->get('a'));
 *
 * @package zxf\Tools
 */
class DataArray implements ArrayAccess, Countable, JsonSerializable, IteratorAggregate
{
    private array $data;

    /**
     * 构造函数
     *
     * @param array $data 初始数组数据
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
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
        return isset($this->data[$offset]);
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
        return $this->data[$offset] ?? null;
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
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * 实现 ArrayAccess 接口的 offsetUnset 方法
     *
     * @param mixed $offset 偏移量
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * 获取包含所有键的数组
     *
     * @return array 包含所有键的数组
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * 获取包含所有值的数组
     *
     * @return array 包含所有值的数组
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * 反向遍历数组对象
     *
     * @return Generator 返回一个生成器用于反向遍历数组对象
     */
    public function reverse(): Generator
    {
        $keys = array_reverse(array_keys($this->data));
        foreach ($keys as $key) {
            yield $key => $this->data[$key];
        }
    }

    /**
     * 实现 Countable 接口的 count 方法
     *
     * @return int 数组元素的数量
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * 实现 JsonSerializable 接口的 jsonSerialize 方法
     * 使得对象可以直接用于 json_encode
     *
     * @return array 返回用于 json 编码的数组
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * 获取所有数据
     *
     * @return array 数组表示
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * 自定义 __toString 方法，使得直接打印对象时输出数组格式的字符串
     *
     * @return string 数组格式的字符串
     */
    public function __toString(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }

    /**
     * 检查数组是否为空
     *
     * @return bool 如果数组为空则返回 true，否则返回 false
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * 获取数组中某个键的值，如果不存在则返回默认值
     *
     * @param mixed $key          要获取的键
     * @param mixed $defaultValue 如果键不存在时返回的默认值
     *
     * @return mixed 键对应的值或默认值
     */
    public function get(mixed $key, mixed $defaultValue = null): mixed
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $defaultValue;
    }

    /**
     * 合并另一个数组到当前数组对象中
     *
     * @param array $array 要合并的数组
     */
    public function merge(array $array): void
    {
        $this->data = array_merge($this->data, $array);
    }

    /**
     * 在数组中搜索给定的值，并返回键
     *
     * @param mixed $value  要搜索的值
     * @param bool  $strict 是否使用严格比较
     *
     * @return string|int|bool 如果找到则返回键，否则返回 null
     */
    public function search(mixed $value, bool $strict = true): string|int|bool
    {
        return array_search($value, $this->data, $strict);
    }

    /**
     * 对数组进行排序
     *
     * @param int $flags 可选的排序标志，如 SORT_ASC、SORT_DESC、SORT_NUMERIC 等
     */
    public function sort(int $flags = SORT_REGULAR): void
    {
        asort($this->data, $flags);
    }

    /**
     * 逆序排序数组并保持索引关联
     *
     * @param int $flags 可选的排序标志，如 SORT_ASC、SORT_DESC、SORT_NUMERIC 等
     */
    public function rsort(int $flags = SORT_REGULAR): void
    {
        arsort($this->data, $flags);
    }

    /**
     * 返回数组的一个片段
     *
     * @param int      $offset 开始的偏移量
     * @param int|null $length 返回的元素个数
     *
     * @return array 返回数组片段
     */
    public function slice(int $offset, int $length = null): array
    {
        return array_slice($this->data, $offset, $length, true);
    }

    /**
     * 使用回调函数对数组的每个元素进行操作，并返回新数组
     *
     * @param callable $callback 回调函数
     *
     * @return array 返回新数组
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->data);
    }

    /**
     * 使用回调函数过滤数组中的元素
     *
     * @param callable $callback 回调函数
     *
     * @return array 返回过滤后的新数组
     */
    public function filter(callable $callback): array
    {
        return array_filter($this->data, $callback);
    }

    /**
     * 设置多个值
     *
     * @param array $values 键值对数组
     */
    public function add(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    // 实现 IteratorAggregate 接口,IteratorAggregate和Iterator接口只能选择一个
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

}
