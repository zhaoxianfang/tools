<?php


namespace zxf\Database;

use Exception;
use zxf\Tools\DataArray;

class Model
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
     * 'get', 'find', 'query' 等方法的返回数据
     */
    protected mixed $resData = [];

    public function __construct()
    {
        self::$db = Db::instance()->table($this->getTableName());
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

    public static function query(): static
    {
        return new static();
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
    private function getPrimaryKeyValue()
    {

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
    public function __call(string $method, mixed $arg)
    {
        $this->resData = call_user_func_array(array(self::$db, $method), ...$arg);
        if (is_array($this->resData)) {
            $this->resData = new DataArray($this->resData);
            // 实现遍历 $this->resData 时返回的是 Model 对象
            $this->resData->setModel($this);
        }
        return $this->resData;
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
    public static function __callStatic(string $method, mixed $arg)
    {
        if (empty(self::$db)) {
            self::$db = Db::instance()->table((new static())->getTableName());
        }
        return call_user_func_array(array(self::$db, $method), ...$arg);
    }
}
