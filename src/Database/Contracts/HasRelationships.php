<?php

namespace zxf\Database\Contracts;

use Exception;
use zxf\Database\Db;
use zxf\Database\Generator\SqlBuildGenerator;
use zxf\Database\Model;

/**
 * 模型关联
 */
trait HasRelationships
{
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
     * @return SqlBuildGenerator
     */
    public function hasMany(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*'): SqlBuildGenerator
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        $targetTableName = is_string($table) ? $table : ($table instanceof Model ? $table->getTableName() : '');
        // 查找被关联表的数据
        return self::query()->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($this->getTableName(), "`{$targetTableName}`.`{$foreignKey}` = `{$this->getTableName()}`.`{$localKey}`");
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
     * @return SqlBuildGenerator
     * @throws Exception
     */
    public function belongsTo(Model|string $table, string $foreignKey = 'target_id', string $ownerKey = 'id', string $field = '*'): SqlBuildGenerator
    {
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && !is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table           = new $table;
            $targetTableName = $table->getTableName();
        }
        $field = empty($field) ? '*' : $field;

        // return self::query()->getDb()
        return $this->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($this->getTableName(), "`{$this->getTableName()}`.`{$foreignKey}` = `{$targetTableName}`.`{$ownerKey}`")
            ->where("`{$this->getTableName()}`.`{$this->primaryKey}`", $this->getPrimaryKeyValue())
            ->limit(1);
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
     * @return SqlBuildGenerator
     * @throws Exception
     */
    public function hasOne(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*'): SqlBuildGenerator
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && !is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table           = new $table;
            $targetTableName = $table->getTableName();
        }

        return self::query()->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($this->getTableName(), "{$targetTableName}.{$foreignKey} = {$this->getTableName()}.{$localKey}")
            ->where("`{$this->getTableName()}`.`{$this->primaryKey}`", $this->getPrimaryKeyValue())
            ->limit(1);
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
     * @return SqlBuildGenerator
     * @throws Exception
     */
    public function belongsToMany(Model|string $table, Model|string $middleTable, string $middleForeignKey = 'user_id', string $targetForeignKey = 'article_id', string $ownerKey = "id", string $middleKey = "id", string $field = '*'): SqlBuildGenerator
    {
        $field = empty($field) ? '*' : $field;

        // 最终查询的目标表名
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && !is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table           = new $table;
            $targetTableName = $table->getTableName();
        }
        // 中间表名
        // 判断字符串类$middleTable是否继承Model类
        if (is_string($middleTable) && !is_subclass_of($middleTable, Model::class)) {
            throw new Exception('参数$middleTable必须是Model类或其子类');
        } else {
            $middleTable     = new $middleTable;
            $middleTableName = $middleTable->getTableName();
        }

        // $field 的每一项都加上表名$aimTableName
        $fields = explode(',', $field);
        foreach ($fields as $key => $value) {
            $fields[$key] = "{$targetTableName}.{$value}";
        }
        return self::query()->getDb()
            ->table($targetTableName)
            ->select($fields)
            ->join($middleTableName, "{$targetTableName}.{$targetForeignKey} = {$middleTableName}.{$middleKey}")
            ->join($this->getTableName(), "{$this->getTableName()}.{$ownerKey} = {$middleTableName}.{$middleForeignKey}")
            ->where("{$middleTableName}.{$middleForeignKey}", $this->getPrimaryKeyValue());
    }

    /**
     * 判断是否是关联关系
     *
     * @param string $name
     *
     * @return mixed
     */
    private function hasRelationAndGet($name): mixed
    {
        if (method_exists($this, $name)) {
            $fun = $this->$name();
            if ($fun instanceof SqlBuildGenerator) {
                return $fun->get();
            }
            return false;
        }
        return false;
    }

    /**
     * 获取关联关系 查询结果
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getRelation(string $name): mixed
    {
        return $this->$name()->get();
    }
}
