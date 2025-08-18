<?php

namespace zxf\Database\Contracts;

use Exception;
use zxf\Database\Generator\SqlGenerator;
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
     * @param  string  $foreignKey  被关联表用来关联本表的外键
     * @param  string  $localKey  当前表的主键
     */
    public function hasMany(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*'): SqlGenerator
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
     * @param  string  $foreignKey  本模型关联被关联表的外键
     * @param  string  $ownerKey  被关联表的主键
     *
     * @throws Exception
     */
    public function belongsTo(Model|string $table, string $foreignKey = 'target_id', string $ownerKey = 'id', string $field = '*'): SqlGenerator
    {
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && ! is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table = new $table;
            $targetTableName = $table->getTableName();
        }
        $field = empty($field) ? '*' : $field;

        // return self::query()->getDb()
        return $this->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($this->getTableName(), "`{$this->getTableName()}`.`{$foreignKey}` = `{$targetTableName}`.`{$ownerKey}`")
            ->where("`{$this->getTableName()}`.`{$this->primaryKey}`", $this->getPrimaryKeyData())
            ->limit(1);
    }

    /**
     * 一对一关联，例如一个用户有一个身份证
     *       Model::hasOne('被关联的表名', '被关联表的外键', '查询字段');
     *       User::hasOne('id_card', 'user_id', 'id,name');
     *       User::hasOne(IdCard::class, 'user_id', 'id',  'id,name');
     *
     * @param  string  $foreignKey  被关联表 用来关联本表的外键
     * @param  string  $localKey  当前表的主键
     *
     * @throws Exception
     */
    public function hasOne(Model|string $table, string $foreignKey = 'target_id', string $localKey = 'id', string $field = '*'): SqlGenerator
    {
        $field = empty($field) ? '*' : $field;
        // 被关联表的表名(目标表名)
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && ! is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table = new $table;
            $targetTableName = $table->getTableName();
        }

        return self::query()->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($this->getTableName(), "{$targetTableName}.{$foreignKey} = {$this->getTableName()}.{$localKey}")
            ->where("`{$this->getTableName()}`.`{$this->primaryKey}`", $this->getPrimaryKeyData())
            ->limit(1);
    }

    /**
     * 远程一对多关联,例如一个用户有多个文章，文章有多个评论，用户可以通过文章获取评论
     *
     * @param  Model|string  $table  例如 评论表（目标表）
     * @param  Model|string  $middleTable  例如 文章表（中间表）
     * @param  string  $middleForeignKey  例如 用户表在文章表的外键 user_id | 中间表关联当前表的外键
     * @param  string  $targetForeignKey  例如 文章表在评论表的外键 article_id | 目标表关联中间表的外键
     * @param  string  $ownerKey  例如 用户表的主键 id | 当前表的主键
     * @param  string  $middleKey  例如 文章表的主键 id | 中间表的主键
     * @param  string  $field  例如 要查询的评论表的字段
     *
     * @throws Exception
     */
    public function belongsToMany(Model|string $table, Model|string $middleTable, string $middleForeignKey = 'user_id', string $targetForeignKey = 'article_id', string $ownerKey = 'id', string $middleKey = 'id', string $field = '*'): SqlGenerator
    {
        $field = empty($field) ? '*' : $field;

        // 最终查询的目标表名
        // 判断字符串类$table是否继承Model类
        if (is_string($table) && ! is_subclass_of($table, Model::class)) {
            throw new Exception('参数$table必须是Model类或其子类');
        } else {
            $table = new $table;
            $targetTableName = $table->getTableName();
        }
        // 中间表名
        // 判断字符串类$middleTable是否继承Model类
        if (is_string($middleTable) && ! is_subclass_of($middleTable, Model::class)) {
            throw new Exception('参数$middleTable必须是Model类或其子类');
        } else {
            $middleTable = new $middleTable;
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
            ->where("{$middleTableName}.{$middleForeignKey}", $this->getPrimaryKeyData());
    }

    /**
     * 远程一对一关联，
     *      例如：一个点赞属于一条评论，一条评论属于一篇文章， 就可以通过这个点赞找到远程关联的唯一文章（ 点赞->评论->文章 ）
     *
     * @param  Model|string  $targetTable  目标表 例：文章表 articles
     * @param  Model|string  $throughTable  中间表 例：评论表 comments
     * @param  string  $throughToLocalKey  中间表关联当前表的外键 例：点赞表关联评论表的外键 comment_id
     * @param  string  $targetToThroughKey  目标表关联中间表的外键 例：评论表关联文章表的外键 article_id
     * @param  string  $throughKey  中间表的主键 例：评论表的主键id
     * @param  string  $targetKey  目标表的主键 例：文章表的主键id
     * @return mixed
     */
    public function hasOneThrough(Model|string $targetTable, Model|string $throughTable, string $throughToLocalKey, string $targetToThroughKey, string $throughKey = 'id', string $targetKey = 'id', string $field = '*')
    {
        $targetTableName = is_string($targetTable) ? $targetTable : ($targetTable instanceof Model ? $targetTable->getTableName() : '');
        $throughTableName = is_string($throughTable) ? $throughTable : ($throughTable instanceof Model ? $throughTable->getTableName() : '');
        $field = empty($field) ? '*' : $field;

        return self::query()->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($throughTableName, "{$throughTableName}.{$targetToThroughKey} = {$targetTableName}.{$targetKey}")
            ->join($this->getTableName(), "{$this->getTableName()}.{$targetToThroughKey} = {$throughTableName}.{$throughKey}")
            ->where("{$throughTableName}.{$throughToLocalKey}", $this->getPrimaryKeyData())
            ->limit(1);
    }

    /**
     * 远程一对多关联，
     *      例如：一个文章有多条评论，一条评论有多个点赞， 就可以通过某个文章找到这个文章所有评论里的点赞 （文章->n个评论->n个点赞）
     *
     * @param  Model|string  $targetTable  目标表 例：点赞表 zan
     * @param  Model|string  $throughTable  中间表 例：评论表 comment
     * @param  string  $throughToLocalKey  中间表关联当前表的外键 例：评论表关联文章表的外键 article_id
     * @param  string  $targetToThroughKey  目标表关联中间表的外键 例：评论表关联文章表的外键 comment_id
     * @param  string  $targetKey  当前表的主键 例：文章表的主键id
     * @param  string  $throughKey  中间表的主键 例：评论表的主键id
     * @return void
     */
    public function hasManyThrough(Model|string $targetTable, Model|string $throughTable, string $throughToLocalKey, string $targetToThroughKey, string $targetKey = 'id', string $throughKey = 'id', string $field = '*')
    {
        $targetTableName = is_string($targetTable) ? $targetTable : ($targetTable instanceof Model ? $targetTable->getTableName() : '');
        $throughTableName = is_string($throughTable) ? $throughTable : ($throughTable instanceof Model ? $throughTable->getTableName() : '');
        $field = empty($field) ? '*' : $field;

        return self::query()->getDb()
            ->table($targetTableName)
            ->select(explode(',', $field))
            ->join($throughTableName, "{$targetTableName}.{$targetToThroughKey} = {$throughTableName}.{$throughKey}")
            ->join($this->getTableName(), "{$throughTableName}.{$throughToLocalKey} = {$this->getTableName()}.{$targetKey} ")
            ->where("{$throughTableName}.{$throughToLocalKey}", $this->getPrimaryKeyData());
    }

    /**
     * 判断是否是关联关系
     */
    private function hasRelationAndGet(string $name): mixed
    {
        if (method_exists($this, $name)) {
            $fun = $this->$name();
            if ($fun instanceof SqlGenerator) {
                return $fun->get();
            }

            return false;
        }

        return false;
    }

    /**
     * 获取关联关系 查询结果
     */
    public function getRelation(string $name): mixed
    {
        return $this->$name()->get();
    }
}
