<?php


namespace zxf\Database;

use zxf\Database\Db;
use zxf\tools\DataArray;

class Model
{

    /**
     * Working instance of Db created earlier
     *
     * @var Db
     */
    private $db;

    /**
     * Primary key for an object. 'id' is a default value.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name for an object. Class name will be used by default
     *
     * @var string
     */
    protected $dbTable;

    /**
     * 查询到的数据集合
     */
    protected $items;

    public $connectionName = 'default';

    public function __construct($data = null)
    {
        $this->db = Db::newQuery();
        $this->db->connect($this->connectionName);
        $this->db->table($this->getTableName());
        if ($data) {
            $this->data = $data;
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
    public function underlineConvert(string $str): string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }

    public static function query()
    {
        return new static();
    }

    /**
     * 填充数据
     */
    public function fill()
    {
        // TODO

    }

    /**
     * 更新或者插入数据
     */
    public function save()
    {
        // TODO
    }

    /**
     * 插入数据
     */
    public function create()
    {
        // TODO
    }

    /**
     * 插入数据 ,失败则抛出异常
     */
    public function createOrFail($id)
    {
        // TODO
    }

    /**
     * 更新数据
     */
    public function update()
    {
        // TODO
    }

    /**
     * 插入或修改数据
     */
    public function createOrUpdate()
    {
        // TODO
    }

    /**
     * 查询一条数据
     */
    public function find($id)
    {
        // TODO
        $res = $this->db->where($this->primaryKey, $id)->first();
        if ($res) {
            $this->setData($res);
        }
        return $this;
    }

    /**
     * 查询一条数据 ,不存在则抛出异常
     */
    public function findOrFail($id)
    {
        // TODO
    }

    /**
     * 一对多关联
     *          例如：一个用户有多个文章
     *          Model::hasMany('被关联的表名', '被关联表的外键', 'Model主键',  '查询字段')->...其他Db支持的查询条件;
     *          User::hasMany('articles', 'user_id', 'id',  'id,title,content')->get();
     *          User::hasMany(Article::class, 'user_id', 'id',  'id,title,content')->where(...)->get();
     *
     * @param        $table
     * @param string $foreignKey 被关联表的外键
     * @param string $localKey   当前表的主键
     * @param string $field
     *
     * @return Db
     */
    public function hasMany($table, $foreignKey, $localKey, $field): Db
    {
        $localTable = $this->getTableName();
        $localKey   = !empty($localKey) ? $localKey : (Db::newQuery())->connect($this->connectionName)->table($localTable)->getPrimaryKey()[0];
        $field      = empty($field) ? '*' : $field;

        $tableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');

        return $this->db->table($tableName)->select($field)->where("`{$tableName}`.`{$foreignKey}` =  `{$localTable}`.`{$localKey}`");
    }

    /**
     * 一个模型从属于另一个模型，例如一个用户从属于一个国家
     *          Model::hasMany('被关联的表名', '被关联表的外键', 'Model主键',  '查询字段');
     *          User::hasMany('country', 'country_id', 'id',  'id,name');
     *          User::hasMany(Country::class, 'country_id', 'id',  'id,name');
     *
     * @param        $table
     * @param        $foreignKey
     * @param string $ownerKey
     * @param string $field
     *
     * @return Db
     */
    public function belongsTo($table, $foreignKey, $ownerKey, $field)
    {
        $localTable = $this->getTableName();
        $ownerKey   = !empty($ownerKey) ? $ownerKey : (Db::newQuery())->connect($this->connectionName)->table($localTable)->getPrimaryKey()[0];
        $field      = empty($field) ? '*' : $field;

        $tableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');

        return $this->db->table($tableName)->select($field)->where("`{$localTable}`.`{$foreignKey}` =  `{$tableName}`.`{$ownerKey}`")->first();
    }

    /**
     * 一对一关联，例如一个用户有一个身份证
     *       Model::hasOne('被关联的表名', '被关联表的外键', '查询字段');
     *       User::hasOne('id_card', 'user_id', 'id,name');
     *       User::hasOne(IdCard::class, 'user_id', 'id',  'id,name');
     *
     * @param        $table
     * @param        $foreignKey
     * @param string $field
     *
     * @return mixed
     */
    public function hasOne($table, $foreignKey, $field = '*')
    {
        $tableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');
        return $this->db->table($tableName)->select($field)->where("{$tableName}.{$foreignKey}", $this->id)->first();
    }

    /**
     * 远程一对多关联,例如一个用户有多个文章，文章有多个评论，用户可以通过文章获取评论
     *
     * @param mixed  $table             例如 评论表（目标表）
     * @param mixed  $throughTable      例如 文章表（中间表）
     * @param string $ownerForeignKey   例如 用户表在文章表的外键 user_id
     * @param string $throughForeignKey 例如 文章表在评论表的外键 article_id
     * @param string $ownerKey          例如 用户表的主键 id
     * @param string $throughKey        例如 文章表的主键 id
     * @param string $field             例如 要查询的评论表的字段
     *
     * @return mixed
     */
    public function hasManyThrough($table, $throughTable, $ownerForeignKey, $throughForeignKey, $ownerKey = "id", $throughKey = "id", $field = '*')
    {
        $ownerTableName  = $this->getTableName();
        $aimTableName    = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : ''); // 查询的目标表名
        $middleTableName = is_string($throughTable) ? $throughTable : ($throughTable instanceof Model ? $throughTable->getTableName() : ''); // 关联的中间表名

        return $this->db->table($aimTableName)
            ->select("{$aimTableName}.*")
            ->join($middleTableName, "{$aimTableName}.{$throughForeignKey} = {$middleTableName}.{$throughKey}")
            ->join($ownerTableName, "{$ownerTableName}.{$ownerKey} = {$middleTableName}.{$ownerForeignKey}")
            ->where("{$middleTableName}.{$ownerForeignKey}", $this->id)
            ->get();
    }

    protected function setData($items = [], $multi = false)
    {
        if ($multi) {
            foreach ($items as $key => $item) {
                $this->items[$key] = new DataArray($item);
            }
        } else {
            $this->items = new DataArray($items);
        }
        return $this;
    }

    public function __get($name)
    {
        if (property_exists($this, 'hidden') && in_array($name, $this->hidden)) {
            return null;
        }

        if (isset($this->items[$name])) {
            return $this->items[$name];
        }

        return null;
    }

    /**
     * Magic setter function
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (property_exists($this, 'hidden') && in_array($name, $this->hidden)) {
            return $this;
        }

        $this->items[$name] = $value;
        return $this;
    }

    /**
     * Catches calls to undefined methods.
     *
     * Provides magic access to private functions of the class and native public Db functions
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     */
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->db, $method), $arg);
    }

    /**
     * Catches calls to undefined static methods.
     *
     * Transparently creating Model class to provide smooth API like name::get() name::orderBy()->get()
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     */
    public static function __callStatic($method, $arg)
    {
        $class = self::class;
        if (method_exists($class, $method)) {
            return call_user_func_array(array($class, $method), $arg);
        }

        if (empty($class->db)) {
            $class = self::query();
        }
        return call_user_func_array(array($class->db, $method), $arg);
    }
}