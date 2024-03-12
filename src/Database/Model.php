<?php


namespace zxf\Database;

use Exception;
use ArrayAccess;
use zxf\Database\Contracts\HasRelationships;
use zxf\Tools\Collection;

class Model implements ArrayAccess
{
    use HasRelationships;

    /**
     * Db 对象
     *
     * @var Db
     */
    private static Db $db;

    /**
     * 表的主键id
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * 表名称，如果不设置则默认为类名的下划线形式
     *
     * @var string
     */
    protected string $tables;

    /**
     * 模型中带字段名称的数据
     *
     * @var array
     */
    protected array $items = [];

    public function __construct(array $data = [])
    {
        self::$db = Db::instance();
        self::$db->table($this->getTableName());
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    // 获取表名
    public function getTableName(): string
    {
        if (empty ($this->tables)) {
            $this->tables = $this->underlineConvert(get_class($this));
        }
        return $this->tables;
    }

    // 获取模型类名(驼峰转下划线)
    private function underlineConvert(string $str): string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }

    public static function query(array $items = []): static
    {
        return new static($items);
    }

    /**
     * 获取当前查询出来的记录的主键对应的值
     */
    public function getPrimaryKeyValue()
    {
        return $this->items[$this->primaryKey] ?? null;
    }

    public function getDb()
    {
        return self::$db;
    }

    /**
     * 填充数据
     *
     * @param array $data
     *
     * @return Model
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            $this->items[$key] = $value;
        }
        return $this;
    }

    /**
     * 把模型转换为数组
     *
     * @return array
     */
    public function toArray(bool $depth = false): array
    {
        return (array)$this->items;
    }

    /**
     * 把数组转换为模型集合对象
     *
     * @param array $items 数组
     *
     * @return Collection 返回一个集合
     * @throws Exception
     */
    public function collection(array $items)
    {
        if (empty($items)) {
            return new Collection();
        }
        if (!in_array($dimension = Collection::getArrayDimension($items), [1, 2])) {
            throw new Exception('数组异常，无法转换为集合对象：仅支持一维或二维数组');
        }

        $instances = [];
        self::$db->fill([]);
        if ($dimension === 1) {
            $model        = self::query();
            $model->items = $items;
            $instances[]  = $model;
        } else {
            foreach ($items as $item) {
                $model        = self::query();
                $model->items = $item;
                $instances[]  = $model;
            }
        }

        return new Collection($instances);
    }

    public function __get(string $name): mixed
    {
        // 判断是否是关联关系
        if ($this->hasRelation($name)) {
            return $this->getRelation($name);
        }
        return $this->items[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->items[$name] = $value;
    }

    /**
     * 调用不存在的方法时，调用Db类的方法
     *
     * @param string $method 调用的方法名
     * @param mixed  $arg    参数
     *
     * @return mixed
     */
    public function __call(string $method, mixed $arg)
    {
        if (!empty($this->items)) {
            // 如果模型中有数据则填充到Db对象中
            self::$db->fill($this->items);
        }
        self::$db->setModal($this);
        return call_user_func_array(array(self::$db, $method), $arg);
    }

    /**
     * 调用不存在的静态方法时，调用Db类的方法
     *
     * @param string $method 调用的方法名
     * @param mixed  $arg    参数
     *
     * @return mixed
     */
    public static function __callStatic(string $method, mixed $arg)
    {
        $model = self::query();
        if (!isset(self::$db) || empty(self::$db)) {
            self::$db = Db::instance();
            self::$db->table($model->getTableName());
        }
        self::$db->setModal($model);
        return call_user_func_array(array(self::$db, $method), $arg);
    }

    // ================================================
    // 以下是 ArrayAccess 接口的方法 用于数组式访问 开始
    // ================================================

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    // ================================================
    // 以上是 ArrayAccess 接口的方法 用于数组式访问 结束
    // ================================================

}
