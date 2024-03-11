<?php

namespace zxf\Tools;

use ArrayAccess;
use Exception;
use IteratorAggregate;
use ArrayIterator;
use Countable;
use JsonSerializable;

/**
 * 集合操作
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    private array $items;

    // 数组的维度
    // 1: 一维数组 eg: [1, 2, 3]
    // 2: 二维数组 eg: [[1, 2, 3], [4, 5, 6]]
    private static int $arraySingle;

    /**
     * 构造函数
     *
     * @param array $items 初始数组数据
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
        // 判断$items是几维数组
        self::$arraySingle = $this->getArrayDimension($items);
    }

    /**
     * 获取数组的维度
     *
     * @param mixed $data 数组
     *
     * @return int 数组的维度
     */
    public static function getArrayDimension(mixed $data): int
    {
        return is_array($data) ? (1 + max(array_map([self::class, 'getArrayDimension'], $data))) : 0;
    }

    //实现 IteratorAggregate 接口,IteratorAggregate和Iterator接口只能选择一个
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
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
        return $this->items;
    }

    // ================================================
    //  魔术方法
    // ================================================

    /**
     * 自定义 __toString 方法，使得直接打印对象时输出数组格式的字符串
     *
     * @return string 数组格式的字符串
     */
    public function __toString(): string
    {
        return json_encode($this->items, JSON_PRETTY_PRINT);
    }

    public function __get(string $name): mixed
    {
        if (self::$arraySingle === 1) {
            return reset($this->items)->$name ?? null;
        }
        throw new Exception($name . ' 属性不存在');
    }

    public function __set(string $name, $value): void
    {
        if (self::$arraySingle === 1) {
            reset($this->items)->$name = $value;
        }
        throw new Exception($name . ' 属性不存在');
    }

    /**
     * 方法调用
     */
    public function __call(string $method, mixed $arg): mixed
    {
        if (self::$arraySingle === 1) {
            $target = reset($this->items);
            if (!empty($target) && is_object($target)) {
                return call_user_func_array(array($target, $method), $arg);
            }
        }
        throw new Exception($method . ' 方法不存在');
    }

    /**
     * 静态方法调用
     */
    public static function __callStatic(string $method, mixed $arg): mixed
    {
        throw new Exception($method . ' 方法不存在');
    }

    // ================================================
    //  其他自定义方法
    // ================================================

    /**
     * 获取所有数据
     *
     * @return array 数组表示
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * 检查数组是否为空
     *
     * @return bool 如果数组为空则返回 true，否则返回 false
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    // 获取集合中的最后一个元素
    public function last(): mixed
    {
        return end($this->items);
    }

    // 获取集合中的第一个元素
    public function first(): mixed
    {
        return reset($this->items);
    }

    // 获取集合中的所有键
    public function keys(): array
    {
        return array_keys($this->items);
    }

    // 获取集合中的所有值
    public function values(): array
    {
        return array_values($this->items);
    }

    // 将集合中的项进行合并，并返回合并后的集合
    public function merge(): array
    {
        return array_merge($this->items, ...func_get_args());
    }

    // 将集合中的项进行反转，并返回反转后的集合
    public function reverse(): array
    {
        return array_reverse($this->items);
    }

    // 遍历集合中的项
    public function each($callback): self
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
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
        return array_map($callback, $this->items);
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
        return array_filter($this->items, $callback);
    }
}
