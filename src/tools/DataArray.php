<?php

namespace zxf\tools;

use ArrayAccess;

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
 * @package zxf\tools
 */
class DataArray implements ArrayAccess
{

    /**
     * 当前配置值
     *
     * @var array
     */
    private $data = [];

    /**
     * data constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->data = $options;
    }

    /**
     * 设置配置项值
     *
     * @param string                    $offset
     * @param string|array|null|integer $value
     */
    public function set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * 获取配置项参数
     *
     * @param string|null $offset
     *
     * @return array|string|null
     */
    public function get($offset = null)
    {
        return $this->offsetGet($offset);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __unset($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    /**
     * 合并数据到对象
     *
     * @param array $data   需要合并的数据
     * @param bool  $append 是否追加数据
     *
     * @return array
     */
    public function merge(array $data, bool $append = true): array
    {
        return $this->data = $append && !empty($this->data) && !empty($data) ? array_merge($this->data, $data) : $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function hasValue($value): bool
    {
        return in_array($value, $this->data);
    }

    public function hasKey($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * 设置配置项值
     *
     * @param string                    $offset
     * @param string|array|null|integer $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * 判断配置Key是否存在
     *
     * @param string $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * 清理配置项
     *
     * @param string|null $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset = null)
    {
        if (is_null($offset)) {
            $this->data = [];
        } else {
            unset($this->data[$offset]);
        }
    }

    /**
     * 获取配置项参数
     *
     * @param string|null $offset
     *
     * @return array|string|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset = null)
    {
        if (is_null($offset)) {
            return $this->data;
        }
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}