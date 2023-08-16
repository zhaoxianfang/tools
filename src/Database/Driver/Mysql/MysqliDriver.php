<?php

namespace zxf\Database\Driver\Mysql;

use Closure;
use \Exception;
use ReflectionClass;
use ReflectionException;
use zxf\Database\Contracts\MysqlInterface;

class MysqliDriver implements MysqlInterface
{
    // mysqli 连接信息
    private $conn;
    private $stmt; // PDO 运行实例
    // 查询的SQL
    protected $query      = '';
    private   $parameters = []; // 参数绑定
    // 错误信息
    protected $error = '';
    // 表名
    protected $table = '';

    private $config = []; // 连接配置信息

    private $fieldStr   = ''; // 解析后的查询字段
    private $joinStr    = ''; // 关联表查询
    private $joinOnStr  = ''; // 关联表查询的 闭包 ON 部分
    private $whereStr   = ''; // 查询条件
    private $groupByStr = ''; // 分组
    private $havingStr  = ''; // 过滤条件
    private $orderByStr = ''; // 排序
    private $limitStr   = ''; // 查询条数

    /**
     * @param string $connectionName
     * @param mixed  ...$args
     *
     * @throws ReflectionException
     */
    public function __construct($connectionName = 'default', ...$args)
    {
        if (!extension_loaded('mysqli')) {
            throw new Exception('不支持的扩展:mysqli');
        }
        if (!empty($params = $this->getConfig($connectionName, ...$args))) {
            $mysqlIc    = new ReflectionClass('mysqli');
            $this->conn = $mysqlIc->newInstanceArgs($params);
            if ($this->conn->connect_error) {
                $this->error = "连接失败: " . $this->conn->connect_error;
                throw new Exception($this->error);
            }
            // 设置字符集
            $this->setCharset();
        }
    }

    // 设置字符集
    public function setCharset(string $charset = 'utf8mb4')
    {
        // 设置字符集为utf8mb4
        $this->conn->prepare("SET NAMES '{$charset}'");
        // $this->conn->set_charset($charset);
        return $this;
    }

    /**
     * 重新示例化
     */
    public static function newQuery()
    {
        return new static();
    }

    /**
     * @param string $connectionName
     * @param        ...$args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function connect($connectionName = 'default', ...$args)
    {
        if (empty($params = $this->getConfig($connectionName, ...$args))) {
            return false;
        }

        if (!extension_loaded('mysqli')) {
            throw new Exception('不支持的扩展:mysqli');
        }
        try {
            $mysqlIc    = new ReflectionClass('mysqli');
            $this->conn = $mysqlIc->newInstanceArgs($params);
            if ($this->conn->connect_error) {
                $this->error = "连接失败: " . $this->conn->connect_error;
                throw new Exception($this->error);
            }
            // 设置字符集
            $this->setCharset();
            return $this;
        } catch (Exception $e) {
            throw new Exception("Database 连接失败：" . $e->getMessage());
        }

    }

    // 获取错误信息
    public function error()
    {
        return !empty($this->conn->error) ? $this->conn->error : $this->error;
    }

    // 获取错误编号
    public function errno()
    {
        return $this->conn->errno;
    }

    // 关闭连接
    public function close()
    {
        $this->conn->close();
    }

    private function getConfig($connectionName = 'default', ...$args)
    {
        if (empty($args) || !is_array($config = $args[0]) || count($config) < 4 || empty($config['host']) || empty($config['dbname']) || empty($config['username']) || !isset($config['password'])) {
            if (!function_exists('config') || empty($config = config('tools_database.mysql.' . $connectionName))) {
                return false;
            }
        }

        $this->config = [
            'hostname' => $config['host'],
            'username' => $config['username'] ?? 'root',
            'password' => $config['password'] ?? '',
            'database' => $config['dbname'] ?? '',
            'port'     => $config['port'] ?? 3306,
            'socket'   => $config['socket'] ?? null,
            // 'charset'  => $config['charset'] ?? 'utf8mb4',
        ];
        return $this->config;
    }

    /**
     * 设置要操作的数据表
     * eg:  table('user')  // 单个字符串参数形式
     *      table('user', 'u')  // 多个字符串参数形式设置别名
     */
    public function table($table, $alias = '')
    {
        $this->table = $alias ? $table . ' AS ' . $alias : $table;
        return $this;
    }

    /**
     * 选择要查询的列
     *
     * eg:   select('id, name, SUM(number) AS sum_num, ...')     // 单个字符串参数形式
     *       select('id', 'name','SUM(number) AS sum_num' ...)   // 多个字符串参数形式
     *       select(['id', 'name','SUM(number) AS sum_num' ...]) // 数组形式
     */
    public function select(...$columns)
    {
        $field = [];
        foreach ($columns as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $field[] = $v;
                }
            }
            if (is_string($value)) {
                $field[] = $value;
            }
        }
        $fieldStr = implode(', ', $field);
        empty($this->fieldStr) ? $this->fieldStr = $fieldStr : $this->fieldStr .= ', ' . $fieldStr;

        $this->fieldStr = empty($this->fieldStr) ? '*' : $this->fieldStr;
        return $this;
    }

    /**
     * 获取 类中的 属性或者方法
     *
     * @param string $field
     * @param        ...$args
     *
     * @return null
     */
    public function getOption(string $field = '', ...$args)
    {
        if (property_exists($this, $field)) {
            return $this->$field;
        }
        if (method_exists($this, $field)) {
            return $this->$field(...$args);
        }
        return null;
    }

    /**
     * 生成查询的sql语句
     */
    public function toSql()
    {
        if (empty($this->table)) {
            $this->throwErr('未指定表名');
        }
        $selectStr   = !empty($this->fieldStr) ? $this->fieldStr : '*';
        $this->query = "SELECT {$selectStr} ";
        $this->query .= "FROM {$this->table} ";
        $this->query .= $this->joinStr ? "{$this->joinStr} " : '';
        $this->query .= $this->whereStr ? "WHERE {$this->whereStr} " : '';
        $this->query .= $this->groupByStr ? "GROUP BY  {$this->groupByStr} " : '';
        $this->query .= $this->havingStr ? "HAVING {$this->havingStr} " : '';
        $this->query .= $this->orderByStr ? "ORDER BY {$this->orderByStr} " : '';
        $this->query .= $this->limitStr ? "LIMIT {$this->limitStr} " : '';

        return $this->query;
    }

    public function throwErr($info = '出错啦！')
    {
        throw new Exception($info);
    }

    // 解析查询条件
    private function parseWhere($operator = 'AND', ...$conditions)
    {
        $whereArr = [];
        if (empty($conditions)) {
            return $this;
        }
        $count = count($conditions);

        // 1、参数是单纯的 1个、2个或3个字符串情况
        if (is_string($conditions[0])) {
            if ($count == 1) {
                // 只传入了一个字符串参数
                $whereArr[] = $conditions[0];
            }
            if ($count == 2) {
                $whereArr[]         = $conditions[0] . ' = ?';
                $this->parameters[] = $conditions[1];
            }
            if ($count > 2) {
                // 如果第伞个参数是闭包
                if ($conditions[2] instanceof Closure && is_string($conditions[1]) && is_callable($func = $conditions[2])) {
                    // 闭包函数
                    $subClass = self::newQuery();
                    $func($subClass);

                    $whereArr[]    = $conditions[0] . ' ' . $conditions[1] . ' ' . '( ' . $subClass->getOption('toSql') . ' )';
                    $subParameters = $subClass->getOption('parameters');
                    !empty($subParameters) && !empty($this->parameters) && $this->parameters = array_merge($this->parameters, $subParameters);
                } else {
                    if (!is_null($conditions[2])) {
                        if (is_array($conditions[2])) {
                            $inStr = ' ( ';
                            foreach ($conditions[2] as $val) {
                                $inStr              .= ' ? ,';
                                $this->parameters[] = $val;
                            };
                            $inStr      = rtrim($inStr, ',') . ' ) ';
                            $whereArr[] = $conditions[0] . ' ' . $conditions[1] . $inStr;
                        } else {
                            $this->parameters[] = $conditions[2];
                            $whereArr[]         = $conditions[0] . ' ' . $conditions[1] . ' ?';
                        }
                    } else {
                        $whereArr[] = $conditions[0] . ' ' . $conditions[1];
                    }
                }
            }
        }

        // 2、参数是数组情况
        if (is_array($conditions[0])) {
            foreach ($conditions as $item) {
                if (is_string($item)) {
                    if ($count < 2) {
                        // 只传入了一个字符串参数
                        $whereArr[] = $item;
                    }
                    if ($count == 2) {
                        $whereArr[]         = $item[0] . ' = ?';
                        $this->parameters[] = $item[1];
                    }
                    if ($count > 2) {
                        $whereArr[]         = $item[0] . ' ' . $item[1] . ' ?';
                        $this->parameters[] = $item[1];
                    }
                }
                if (is_array($item)) {
                    foreach ($item as $row) {
                        $c = count($row);
                        if ($c == 1) {
                            $whereArr[] = $row[0];
                        }
                        if ($c == 2) {
                            $whereArr[]         = $row[0] . ' = ?';
                            $this->parameters[] = $row[1];
                        }
                        if ($c > 2) {
                            $whereArr[]         = $row[0] . ' ' . $row[1] . ' ?';
                            $this->parameters[] = $row[2];
                        }
                    }
                }
            }
        }

        // 3、参数是闭包函数情况
        if ($conditions[0] instanceof Closure) {
            // 闭包函数
            $fun      = $conditions[0];
            $subClass = self::newQuery();
            $fun($subClass);
            $whereArr[]    = '( ' . $subClass->getOption('whereStr') . ' )';
            $subParameters = $subClass->getOption('parameters');
            !empty($subParameters) && !empty($this->parameters) && $this->parameters = array_merge($this->parameters, $subParameters);
        }

        $whereStr       = implode(' AND ', $whereArr);
        $this->whereStr = empty($this->whereStr) ? $whereStr : $this->whereStr . ' ' . $operator . ' ' . $whereStr;
        return $this;
    }

    /**
     * 添加 WHERE 条件
     * eg: where('id = 1')
     *     where('id',1)
     *     where([ ['id',1], ['name','like','%威四方%'], ['status','<>',1] ])
     *     where(function($query){
     *        $query->where('age','>',21);
     *     })
     */
    public function where(...$conditions)
    {
        $this->parseWhere('AND', ...$conditions);
        return $this;
    }

    /**
     * @param $field
     * @param $data
     *
     * @return mixed
     */
    public function whereIn($field, $data)
    {
        $this->parseWhere('AND', $field, 'IN', $data);
        return $this;
    }

    /**
     * @param $field
     * @param $data
     *
     * @return mixed
     */
    public function whereNotIn($field, $data)
    {
        $this->parseWhere('AND', $field, 'NOT IN', $data);
        return $this;
    }

    /**
     * @param ...$conditions
     *
     * @return mixed
     */
    public function orWhere(...$conditions)
    {
        $this->parseWhere('OR', ...$conditions);
        return $this;
    }

    /**
     * 两个字段比较
     *
     * @param        $first
     * @param string $operator
     * @param null   $second
     * @param string $boolean
     *
     * @return MysqliDriver
     */
    public function whereColumn($first, $operator = '=', $second = null, $boolean = 'AND')
    {
        if (is_null($second)) {
            $whereStr = "`{$first}` IS NULL ";
        } else {
            $whereStr = "`{$first}` $operator `{$second}` ";
        }
        $this->whereStr = empty($this->whereStr) ? $whereStr : $this->whereStr . ' ' . $boolean . ' ' . $whereStr;
        return $this;
    }

    /**
     * @param $first
     * @param $operator
     * @param $second
     *
     * @return mixed
     */
    public function orWhereColumn($first, $operator = '=', $second = null)
    {
        $this->whereColumn($first, $operator, $second, $boolean = 'OR');
        return $this;
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function whereNull($field)
    {
        $this->parseWhere('AND', $field, 'IS NULL', null);
        return $this;
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function whereNotNull($field)
    {
        $this->parseWhere('AND', $field, 'IS NOT NULL', null);
        return $this;
    }

    /**
     * 传入的$column值存在时才执行
     *
     * @param $column
     * @param $callback
     *
     * @return MysqliDriver
     */
    public function when($column, $callback)
    {
        if (!empty($column) && $callback instanceof Closure && is_callable($callback)) {
            $callback($this, $column);
        }
        return $this;
    }

    /**
     * JOIN 的闭包 ON 查询部分
     *
     * @param ...$args
     */
    public function on(...$args)
    {
        $count = count($args);
        if ($count == 1) {
            $joinOnStr = $args[0];
        }
        if ($count == 2) {
            $joinOnStr = $args[0] . ' = ' . $args[1];
        }
        if ($count == 3) {
            $joinOnStr = $args[0] . ' ' . $args[1] . ' ' . $args[2];
        }

        if ($this->joinOnStr) {
            $this->joinOnStr .= ' AND ' . $joinOnStr;
        } else {
            $this->joinOnStr = $joinOnStr;
        }
        return $this;
    }

    // 解析多表关联查询
    private function parseJoin($operator = 'INNER', ...$conditions)
    {
        if ($conditions[1] instanceof Closure) {
            $fun      = $conditions[1];
            $subClass = self::newQuery();
            $fun($subClass);
            $joinOnStr    = $subClass->getOption('joinOnStr');
            $joinWhereStr = $subClass->getOption('whereStr');

            $joinStr = "{$operator} JOIN {$conditions[0]}";
            if ($joinOnStr) {
                $joinStr .= " ON {$subClass->getOption('joinOnStr')} ";
            }
            if ($joinWhereStr) {
                $this->parseWhere('AND', $joinWhereStr);
                // $joinStr .= " WHERE {$joinWhereStr} ";
            }

            $subParameters = $subClass->getOption('parameters');
            !empty($subParameters) && !empty($this->parameters) && $this->parameters = array_merge($this->parameters, $subParameters);
        } else {
            $joinStr = "{$operator} JOIN {$conditions[0]} ON {$conditions[1]}";
        }

        $this->joinStr = empty($this->joinStr) ? $joinStr : $this->joinStr . ' ' . $joinStr;

        return $this;
    }

    /**
     * 多表关联查询
     *
     * eg:  join('table_name AS t','table1.id = t.table1_id')
     *
     */
    public function join(...$joins)
    {
        $this->parseJoin('INNER', ...$joins);
        return $this;
    }

    /**
     * @param ...$joins
     *
     * @return mixed
     */
    public function leftJoin(...$joins)
    {
        $this->parseJoin('LEFT', ...$joins);
        return $this;
    }

    /**
     * @param ...$joins
     *
     * @return mixed
     */
    public function rightJoin(...$joins)
    {
        $this->parseJoin('RIGHT', ...$joins);
        return $this;
    }

    /**
     * @param $joins
     *
     * @return mixed
     */
    public function fullJoin($joins)
    {
        $this->parseJoin('FULL', ...$joins);
        return $this;
    }

    /**
     * 分组查询
     *
     * @param ...$columns
     *
     * @return mixed
     */
    public function groupBy(...$columns)
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                $groupByStr = implode(', ', $column);
            } else {
                $groupByStr = $column;
            }
            $this->groupByStr = empty($this->groupByStr) ? $groupByStr : $this->groupByStr . ', ' . $groupByStr;
        }
        return $this;
    }

    /**
     * @param ...$args
     *
     * @return mixed
     */
    public function having(...$args)
    {
        $count = count($args);
        if ($count == 1) {
            $havingStr = $args[0];
        }
        if ($count == 2) {
            $havingStr = $args[0] . ' = ' . $args[1];
        }
        if ($count == 3) {
            $havingStr = $args[0] . ' ' . $args[1] . ' ' . $args[2];
        }
        if ($this->havingStr) {
            $this->havingStr .= ' AND ' . $havingStr;
        } else {
            $this->havingStr = $havingStr;
        }
        return $this;
    }

    /**
     * 排序功能
     *
     * @param ...$columns
     */
    public function orderBy(...$columns)
    {
        $orderByArr = [];
        if (is_string($columns[0])) {
            $orderByArr[] = $columns[0] . ' ' . (!empty($columns[1]) ? $columns[1] : ' ASC');
        } else {
            foreach ($columns as $column) {
                if (is_array($column)) {
                    foreach ($column as $field => $direction) {
                        $orderByArr[] = $field . ' ' . $direction;
                    }
                } else {
                    $orderByArr[] = $column . ' ASC';
                }
            }
        }
        $orderByStr       = implode(', ', $orderByArr);
        $this->orderByStr = !empty($orderByArr) && !empty($this->orderByStr) ? $this->orderByStr . ', ' . $orderByStr : $this->orderByStr . $orderByStr;
        return $this;
    }

    /**
     * @param mixed ...$args
     *
     * @return MysqliDriver
     */
    public function limit(...$args)
    {
        if (empty($args)) {
            return $this;
        }
        $count = count($args);
        if ($count == 1) {
            $this->limitStr = $args[0];
        } else {
            $this->limitStr = $args[0] . ', ' . $args[1];
        }
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function execute()
    {
        // 准备预处理语句
        empty($this->query) && $this->toSql();
        $stmt = $this->conn->prepare($this->query);
        if ($stmt) {
            $this->stmt = $stmt;
            // 绑定参数并执行查询
            $this->bindParam();
            $stmt->execute();
            $this->reset();
            return $stmt;
        } else {
            $this->reset();
            throw new Exception("数据库错误：" . $this->conn->error);
        }
    }

    private function bindParam()
    {
        //"i": 表示整数类型
        //"d": 表示双精度浮点数类型
        //"s": 表示字符串类型
        //"b": 表示二进制数据类型（例如 BLOB）

        $bindStr = '';
        foreach ($this->parameters as $value) {
            $bindStr .= is_numeric($value) ? 'd' : 's';
        }
        $this->stmt->bind_param($bindStr, ...array_values($this->parameters));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get()
    {
        $result = $this->execute()->get_result();
        // 处理结果集
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // 处理每一行结果
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 获取第一条结果
     */
    public function first()
    {
        $this->limit(1);
        $result = $this->execute()->get_result();
        return $result->fetch_assoc();
    }

    /**
     * @return mixed
     */
    public function exists()
    {
        $this->fieldStr = ' 1 ';
        return (bool)$this->first();
    }

    /**
     * @return mixed
     */
    public function doesntExist()
    {
        return !$this->exists();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function insert($data)
    {
        try {
            $columns          = implode(', ', array_keys($data));
            $placeholders     = implode(', ', array_fill(0, count($data), '?'));
            $this->query      = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $this->parameters = array_values($data);
            // return $this->execute()->affected_rows;
            return $this->execute()->insert_id;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return false|mixed
     */
    public function insertGetId($data)
    {
        return $this->insert($data);
    }

    /**
     * @return mixed
     */
    public function getLastInsertedId()
    {
        return $this->conn->insert_id;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return !empty($this->conn->error) ? $this->conn->error : $this->error;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function update($data)
    {
        try {
            $set              = implode(' = ?, ', array_keys($data)) . ' = ?';
            $this->query      = "UPDATE {$this->table} SET $set";
            $this->query      .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $parameters       = array_values($data);
            $this->parameters = empty($this->parameters) ? $parameters : array_merge($parameters, $this->parameters);
            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function batchInsertAndGetIds($data)
    {
        try {
            $ids = [];
            $this->transaction(function ($query) use ($data, &$ids) {
                foreach ($data as $row) {
                    $ids[] = $query->insertGetId($row);
                }
            });
            return $ids;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function batchInsert($data)
    {
        try {
            $this->transaction(function ($query) use ($data) {
                foreach ($data as $row) {
                    $query->insertGetId($row);
                }
            });
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param array  $data
     * @param string $byColumnField
     *
     * @return mixed
     */
    public function batchUpdate(array $data, string $byColumnField = 'id')
    {
        try {
            $this->parameters = [];

            // 先获取所有要更新的字段
            $firstRow    = $data[0];
            $columnField = [];
            foreach ($firstRow as $itemField => $itemValue) {
                if ($itemField == $byColumnField) {
                    continue;
                }
                $columnField[] = $itemField;
            }
            $updateFieldsData = [];

            // 获取 $byColumnField 列的值作为关联数组的键
            $keys = array_column($data, $byColumnField);

            foreach ($columnField as $fieldName) {
                // 将 $byColumnField 列的值与 $fieldName 列的值关联起来创建新的关联数组
                $updateFieldsData[$fieldName] = array_combine($keys, array_column($data, $fieldName));
            }

            $set = [];
            foreach ($updateFieldsData as $field => $columnData) {
                $whenStr = '';
                foreach ($columnData as $keyVal => $value) {
                    $whenStr            .= "WHEN $byColumnField = $keyVal THEN ? ";
                    $this->parameters[] = $value;
                }
                $set[] = "$field = CASE $whenStr END";
            }

            $this->query = "UPDATE {$this->table} SET " . implode(", ", $set);
            $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';

            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $column
     * @param $amount
     *
     * @return mixed
     */
    public function increment($column, $amount = 1)
    {
        $this->query = "UPDATE {$this->table} SET $column = $column + $amount";
        $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
        return $this->execute()->affected_rows;
    }

    /**
     * @param $column
     * @param $amount
     *
     * @return mixed
     */
    public function decrement($column, $amount = 1)
    {
        return $this->increment($column, -$amount);
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        if (empty($this->whereStr) && empty($this->limitStr)) {
            $this->throwErr('删除数据时必须使用 where() 或 limit() 方法, 否则会清空表数据');
        }
        try {
            $this->query = "DELETE FROM {$this->table}";
            $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $this->query .= $this->limitStr ? "LIMIT {$this->limitStr} " : '';
            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $isolationLevel
     *
     * @return mixed
     */
    public function setTransactionIsolation($isolationLevel)
    {
        $this->query("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        return $this;
    }

    /**
     * @param $sql
     *
     * @return mixed
     */
    public function query($sql = '')
    {
        return $this->conn->query($sql);
    }

    /**
     * @param $sql
     */
    public function exec($sql = '')
    {
        return (bool)$this->query($sql);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public function quote($string)
    {
        return $this->conn->real_escape_string($string);
    }

    /**
     * @return mixed
     */
    public function beginTransaction()
    {
        $this->conn->autocommit(false);
        // return $this->conn->begin_transaction();
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        $res = $this->conn->commit();
        // 恢复自动提交
         $this->conn->autocommit(true);
        return $res;
    }

    /**
     * @return mixed
     */
    public function rollback()
    {
        $res = $this->conn->rollback();
        // 恢复自动提交
         $this->conn->autocommit(true);
        return $res;
    }

    /**
     * @param $callback
     *
     * @return mixed
     * @throws Exception
     */
    public function transaction($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception("事务执行失败：参数必须是一个匿名函数");
        }

        $this->beginTransaction();
        try {
            $callback($this);
            $this->commit();
            $this->reset();
        } catch (Exception $e) {
            $this->reset();
            $this->rollback();
            throw new Exception("事务回滚：" . $e->getMessage());
        }
    }

    /**
     * 事务状态查询
     */
    public function inTransaction()
    {
        // mysqli 不支持事务状态查询，只能通过判断是否开启了
        return false;
    }

    /**
     * 清除查询条件和参数
     */
    public function reset()
    {
        $this->query      = ''; // 最后一次查询的 SQL 语句
        $this->parameters = []; // 参数绑定

        $this->fieldStr = ''; // 解析后的查询字段

        $this->joinStr    = ''; // 关联表查询
        $this->joinOnStr  = ''; // 关联表查询的 闭包 ON 部分
        $this->whereStr   = ''; // 查询条件
        $this->groupByStr = ''; // 分组
        $this->havingStr  = ''; // 过滤条件
        $this->orderByStr = ''; // 排序
        $this->limitStr   = ''; // 查询条数

        $this->union = []; // 联合查询

        // 不清空的字段
        // $this->table = ''; // 当前操作的数据表
        // $this->error = ''; // 异常信息

    }

    /**
     * @param $closure
     *
     * @return mixed
     */
    public function closure($closure)
    {
        $this->query = call_user_func($closure, $this->query);
        return $this;
    }

    /**
     * @param $limit
     * @param $currentPage
     *
     * @return mixed
     */
    public function paginate($limit = 10, $currentPage = 1)
    {
        $offset = ($currentPage - 1) * $limit;
        $this->limit($offset, $limit);
        return $this->get();
    }

    /**
     * 获取数据表字段信息
     */
    public function getColumns()
    {
        $this->query      = "DESCRIBE {$this->table}";
        $this->parameters = [];
        return $this->execute()->get_result();
    }

    /**
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->conn->last_query;
    }

    /**
     * 遍历查询结果的方法
     *
     * @param $callback
     *
     * @return mixed
     * @throws Exception
     */
    public function each($callback)
    {
        $stmt   = $this->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $callback($row);
        }
        $stmt->close();
        return $this;
    }

    /**
     * 获取数据表主键列名
     */
    public function getPrimaryKey()
    {
        // 使用 SHOW KEYS 查询主键信息
        $this->query      = "SHOW KEYS FROM {$this->table} WHERE Key_name = 'PRIMARY'";
        $this->parameters = [];
        $primaryKeyRows   = $this->execute()->get_result();
        // 获取结果集中的主键列名
        $primaryKeyColumnNames = [];
        foreach ($primaryKeyRows as $row) {
            $primaryKeyColumnNames[] = $row['Column_name'];
        }
        return $primaryKeyColumnNames;
    }

    /**
     * 获取数据表索引列信息
     */
    public function getIndexes()
    {
        $this->query      = "SHOW INDEX FROM {$this->table}";
        $this->parameters = [];
        $indexes          = $this->execute()->get_result();

        // 获取结果集中的索引列名
        $indexColumnNames = [];
        foreach ($indexes as $row) {
            $indexColumnNames[] = $row['Column_name'];
        }
        return $indexColumnNames;
    }

    // 添加获取单个值的聚合查询方法
    private function aggregate($function, $column)
    {
        try {
            $this->fieldStr = "$function($column)";
            empty($this->query) && $this->toSql();
            $result = $this->execute()->get_result();
            $row    = $result->fetch_assoc();
            return (int)current($row);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取结果数量
     */
    public function count($column = '*')
    {
        return $this->aggregate('COUNT', $column);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function max($column)
    {
        return $this->aggregate('MAX', $column);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function min($column)
    {
        return $this->aggregate('MIN', $column);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function avg($column)
    {
        return $this->aggregate('AVG', $column);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function sum($column)
    {
        return $this->aggregate('SUM', $column);
    }
}
