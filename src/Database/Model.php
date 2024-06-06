<?php


namespace zxf\Database;

use Exception;
use ArrayAccess;
use zxf\Database\Contracts\HasRelationships;
use zxf\Tools\Collection;

abstract class Model implements ArrayAccess
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
    protected string $table;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected string $connection = 'default';

    /**
     * 模型中带字段名称的数据
     *
     * @var array
     */
    protected array $items = [];

    /**
     * 模型中的修改数据
     *
     * @var array
     */
    protected array $changeData = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);

        $this->initDb();
    }

    /**
     * 初始化Db对象
     *
     * @return void
     * @throws Exception
     */
    private function initDb(): void
    {
        self::$db = Db::connection($this->connection);
        self::$db->table($this->getTableName());

        self::$db->setModal($this);
    }

    public function getDb(): Db
    {
        return self::$db;
    }

    // 获取模型表名
    public function getTableName(): string
    {
        if (empty ($this->table)) {
            $this->table = underline_convert(class_basename(get_class($this)));
        }
        return $this->table;
    }

    public static function query(array $items = []): static
    {
        return new static($items);
    }

    /**
     * 填充数据
     *
     * @param array $data
     *
     * @return Model
     */
    public function fill(array $data): static
    {
        $this->changeData = [];
        $this->items      = [];
        foreach ($data as $key => $value) {
            $this->items[$key]      = $value;
            $this->changeData[$key] = $value;
        }
        return $this;
    }

    /**
     * 获取模型数据
     *
     * @param string|null $key
     *
     * @return array|mixed|null
     */
    public function getItems(string $key = null): mixed
    {
        if ($key) {
            return $this->items[$key] ?? null;
        }
        return $this->items;
    }

    /**
     * 把模型转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(): bool|string
    {
        return json_encode($this->items);
    }

    public function reset(): static
    {
        $this->items      = [];
        $this->changeData = [];
        $this->initDb();
        return $this;
    }

    public function clone(): static
    {
        return clone $this;
    }

    /**
     * 把数组转换为模型集合对象
     *
     * @param array $items 数组
     *
     * @return Collection 返回一个集合
     * @throws Exception
     */
    public function collect(array $items): Collection
    {
        if (empty($items)) {
            return new Collection();
        }
        if (!in_array($dimension = Collection::getArrayDimension($items), [1, 2])) {
            throw new Exception('数组异常，无法转换为集合对象：仅支持一维或二维数组');
        }

        $instances = [];

        if ($dimension === 1) {
            $this->items = $items;
            $this->getDb()->fill($items);
            $instances[] = clone $this;
        } else {
            foreach ($items as $item) {
                $this->items = $item;
                $this->getDb()->fill($item);
                // $instances[]  = $this; 会导致所有的 $instances 都是最后一个 $this，所以需要使用 clone
                $instances[] = clone $this;
            }
        }

        return new Collection($instances);
    }


    public function __get(string $name): mixed
    {
        // 判断是否是关联关系
        if (($res = $this->hasRelationAndGet($name)) !== false) {
            return $res;
        }
        if (isset($this->changeData[$name])) {
            return $this->changeData[$name];
        }
        return $this->items[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (!empty($this->primaryKey) && $name == $this->primaryKey) {
            // 不允许 修改/设置 主键
            return;
        }
        $this->items[$name]      = $value;
        $this->changeData[$name] = $value;
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
     * @throws Exception
     */
    public static function __callStatic(string $method, mixed $arg)
    {
        $model = self::query();
        if (!isset(self::$db) || empty(self::$db)) {
            self::$db = Db::connection();
        }
        self::$db->table($model->getTableName());
        self::$db->setModal($model);
        return call_user_func_array(array(self::$db, $method), $arg);
    }

    // ================================================
    // 以下是 ArrayAccess 接口的方法 用于数组式访问 开始
    // 作用 ： 它允许对象像数组一样被访问
    // ================================================

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
        $this->items[$offset] = $value;
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

    // ================================================
    // 以上是 ArrayAccess 接口的方法 用于数组式访问 结束
    // ================================================


    /**
     * 获取当前查询出来的记录的主键对应的值
     */
    public function getPrimaryKeyData()
    {
        return $this->items[$this->primaryKey] ?? null;
    }

    /**
     * 获取当前模型中修改的数据
     *
     * @return array
     */
    public function getChangeData(): array
    {
        return $this->changeData;
    }

    /**
     * 获取当前模型中的主键数据
     *
     * @return array
     */
    public function getKeyValue(): array
    {
        if ($keyValue = $this->getPrimaryKeyData()) {
            return ['column' => $this->primaryKey, 'value' => $keyValue];
        }
        return [];
    }

}
