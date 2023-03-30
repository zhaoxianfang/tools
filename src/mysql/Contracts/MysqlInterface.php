<?php

namespace zxf\mysql\Contracts;

use Closure;

interface  MysqlInterface
{
    // 构造函数类
    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = 3306, $charset = 'utf8mb4', $socket = null);

    // 设置类型
    public function setCharset(string $charset = 'utf8mb4');

    public function reset();

    // 设置表名
    public function table(string $tableName = '');

    // 查询字段
    public function field(array $columns = ["*"]);

    public function insert(array $data);

    public function update($data);

    public function delete();

    public function count(string $field = '*');

    public function sum(string $field = '*');

    public function avg(string $field = '*');

    public function max(string $field = '*');

    public function min(string $field = '*');

    // 子查询
    public function subQuery($table, $columns = "*", $where = "", $limit = "", $offset = "", $orderBy = "", $groupBy = "");

    public function lockForUpdate($table, $data, $where = "");

    // 批量插入数据
    public function insertBatch(array $data);

    // 批量更新数据
    public function updateBatch(array $data);

    // 事务操作
    public function transaction($callback): bool;

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
     * @param string               $type
     *
     * @return mixed
     */
    public function where($column, $operator = null, $value = null, $type = 'and');

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
     * @param Closure|string|array $column
     * @param mixed                $value
     *
     * @return $this
     */
    public function whereNot($column, $value = null);

    /**
     * 添加一个 "where in" 闭包查询
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return $this
     */
    public function whereIn(string $column, $value = null);

    /**
     * Set the "limit" value of the query.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $offset = 0, int $limit = 10);

    /**
     * This method allows you to specify multiple (method chaining optional) GROUP BY statements for SQL queries.
     *
     * @param string|array $groupByField The name of the database field.
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
     * @param array $data
     *
     * @return bool
     */
    public function save(array $data = []);


    public function query($sql);

    public function toSQL(): string;

    public function toArray(): array;

    public function toJson(): string;
}