<?php

namespace zxf\mysql\Contracts;

use Closure;
use Exception;

interface  MysqlInterface
{
    // 构造函数类
    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = 3306, $charset = 'utf8mb4', $socket = null);

    // 设置类型
    public function setCharset(string $charset = 'utf8mb4');

    public function insert(array $data);

    public function update($table, $data, $where = "");

    public function delete($table, $where = "");

    public function count($table, $where = "");

    public function sum($table, $column, $where = "");

    public function avg($table, $column, $where = "");

    public function max($table, $column, $where = "");

    public function min($table, $column, $where = "");

    public function hasMany($table, $foreign_key, $where = "");

    public function belongsTo($table, $foreign_key, $where = "");

    public function hasOne($table, $foreign_key, $where = "");

    public function hasManyThrough($table, $through_table, $foreign_key, $through_foreign_key, $where = "");

    public function preload($table, $foreign_key, $where = "");

    // 子查询
    public function subQuery($table, $columns = "*", $where = "", $limit = "", $offset = "", $orderBy = "", $groupBy = "");

    public function lockForUpdate($table, $data, $where = "");

    // 批量插入数据
    public function insertBatch($tableName, $data);

    // 批量更新数据
    public function updateBatch($tableName, $data, $primaryKey);

    // 事务操作
    public function transaction($callback): bool;

    // 批量更新
    public function batchUpdate($tableName, $data, $where);

    // 当 $column 不为空时执行闭包
    public function when($column, Closure $callback);

    // 当 $column 为空时执行闭包
    public function whenNull($column, Closure $callback);

    /**
     * 条件过滤
     *
     * @param Closure|string|array $column
     * @param mixed                $operator
     * @param mixed                $value
     * @param string               $boolean
     *
     * @return mixed
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and');

    /**
     * Add an "or where" clause to the query.
     *
     * @param Closure|array|string $column
     * @param mixed                $operator
     * @param mixed                $value
     *
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null);

    /**
     * 添加一个 "where not" 闭包查询
     *
     * @param \Closure|string|array $column
     * @param mixed                 $operator
     * @param mixed                 $value
     * @param string                $boolean
     *
     * @return $this
     */
    public function whereNot($column, $operator = null, $value = null, $boolean = 'and');

    /**
     * 添加一个 "where in" 闭包查询
     *
     * @param string $column
     * @param mixed  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false);

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     *
     * @return $this
     */
    public function limit($value);

    /**
     * This method allows you to specify multiple (method chaining optional) GROUP BY statements for SQL queries.
     *
     * @param string $groupByField The name of the database field.
     */
    public function groupBy($groupByField);

    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string $columns
     */
    public function get($columns = ['*']);

    /**
     * Execute the query and get the first result.
     *
     * @param array|string $columns
     *
     * @return object|static|null
     */
    public function first($columns = ['*']);

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     *
     * @return $this
     *
     */
    public function fill(array $attributes);

    /**
     * Save the model to the database.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = []);

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     *
     * @return $this
     */
    public function select($columns = ['*']);
}