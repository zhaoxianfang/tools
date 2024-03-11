<?php


namespace zxf\Database;

use Exception;
use ArrayAccess;
use zxf\Tools\Collection;

class Model implements ArrayAccess
{
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
    protected string $dbTable;

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
        if (empty ($this->dbTable)) {
            $this->dbTable = $this->underlineConvert(get_class($this));
        }
        return $this->dbTable;
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
     * 一对多关联
     *          例如：一个用户有多个文章
     *          Model::hasMany('被关联的表名', '被关联表用来关联本表的外键', '当前Model主键',  '查询字段')->...其他Db支持的查询条件;
     *          User::hasMany('articles', 'user_id', 'id',  'id,title,content')->get();
     *          User::hasMany(Article::class, 'user_id', 'id',  'id,title,content')->where(...)->get();
     *
     * @param Model|string $table
     * @param string       $foreignKey 被关联表用来关联本表的外键
     * @param string       $localKey   当前表的主键
     * @param string       $field
     *
     * @return Db
     */
    public function hasMany(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*'): Db
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        $targetTableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');
        // 查找被关联表的数据
        return self::$db->table($targetTableName)
            ->select(explode(',', $field))
            ->where("`{$targetTableName}`.`{$foreignKey}`", "`{$this->getTableName()}`.`{$localKey}`")
            ->get();
    }

    /**
     * 一个模型从属于另一个模型，例如一个用户从属于一个国家
     *          Model::hasMany('被关联的表名', '被关联表的外键', '当前Model主键',  '查询字段');
     *          User::hasMany('country', 'country_id', 'id',  'id,name');
     *          User::hasMany(Country::class, 'country_id', 'id',  'id,name');
     *
     * @param Model|string $table
     * @param string       $foreignKey 本模型关联被关联表的外键
     * @param string       $ownerKey   被关联表的主键
     * @param string       $field
     *
     * @return Db
     */
    public function belongsTo(Model|string $table, string $foreignKey = 'target_id', string $ownerKey = 'id', string $field = '*')
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        $targetTableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');

        return self::$db->table($targetTableName)
            ->select(explode(',', $field))
            ->where("`{$this->getTableName()}`.`{$foreignKey}`", "`{$targetTableName}`.`{$ownerKey}`")
            ->find();
    }

    /**
     * 一对一关联，例如一个用户有一个身份证
     *       Model::hasOne('被关联的表名', '被关联表的外键', '查询字段');
     *       User::hasOne('id_card', 'user_id', 'id,name');
     *       User::hasOne(IdCard::class, 'user_id', 'id',  'id,name');
     *
     * @param Model|string $table
     * @param string       $foreignKey 被关联表 用来关联本表的外键
     * @param string       $localKey   当前表的主键
     * @param string       $field
     *
     * @return mixed
     */
    public function hasOne(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*')
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        $targetTableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');
        return self::$db->table($targetTableName)
            ->select(explode(',', $field))
            ->where("{$targetTableName}.{$foreignKey}", "`{$this->getTableName()}`.`{$localKey}`")
            ->find();
    }

    /**
     * 远程一对多关联,例如一个用户有多个文章，文章有多个评论，用户可以通过文章获取评论
     *
     * @param Model|string $table            例如 评论表（目标表）
     * @param Model|string $middleTable      例如 文章表（中间表）
     * @param string       $middleForeignKey 例如 用户表在文章表的外键 user_id | 中间表关联当前表的外键
     * @param string       $targetForeignKey 例如 文章表在评论表的外键 article_id | 目标表关联中间表的外键
     * @param string       $ownerKey         例如 用户表的主键 id | 当前表的主键
     * @param string       $middleKey        例如 文章表的主键 id | 中间表的主键
     * @param string       $field            例如 要查询的评论表的字段
     *
     * @return mixed
     * @throws Exception
     */
    public function belongsToMany(Model|string $table, Model|string $middleTable, string $middleForeignKey = 'user_id', string $targetForeignKey = 'article_id', string $ownerKey = "id", string $middleKey = "id", string $field = '*')
    {
        $field = empty($field) ? '*' : $field;

        // 最终查询的目标表名
        $targetTableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : ''); // 查询的目标表名
        // 中间表名
        $middleTableName = is_string($middleTable) ? $middleTable : ($middleTable instanceof Model ? $middleTable->getTableName() : ''); // 关联的中间表名

        // $field 的每一项都加上表名$aimTableName
        $fields = explode(',', $field);
        foreach ($fields as $key => $value) {
            $fields[$key] = "{$targetTableName}.{$value}";
        }
        return self::$db->table($targetTableName)
            ->select($fields)
            ->join($middleTableName, "{$targetTableName}.{$targetForeignKey} = {$middleTableName}.{$middleKey}")
            ->join($this->getTableName(), "{$this->getTableName()}.{$ownerKey} = {$middleTableName}.{$middleForeignKey}")
            ->where("{$middleTableName}.{$middleForeignKey}", $this->getPrimaryKeyValue())
            ->get();
    }

    /**
     * 获取当前查询出来的记录的主键对应的值
     */
    public function getPrimaryKeyValue()
    {
        return $this->items[$this->primaryKey] ?? null;
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

    public function toArray(): array
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
        if (!in_array($dimension = Collection::getArrayDimension($items), [1, 2])) {
            throw new Exception('数组异常，无法转换为集合对象：仅支持一维或二维数组');
        }

        $instances = [];
        if ($dimension === 1) {
            $model = self::query();
            $model->fill($items);
            $instances[] = $model;
        } else {
            foreach ($items as $item) {
                $model = self::query();
                $model->fill($item);
                $instances[] = $model;
            }
        }

        return new Collection($instances);
    }

    public function __get(string $name): mixed
    {
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
        if (isset(self::$db) || empty(self::$db)) {
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
