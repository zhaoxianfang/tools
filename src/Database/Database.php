<?php

// namespace zxf\Database;

//use \Closure;
//use \Exception;
//use \PDO;
//use \PDOException;

class Database
{
    private $pdo; // PDO 实例
    private $query; // 当前 SQL 查询
    private $parameters = []; // 参数绑定

    private $fieldStr = ''; // 解析后的查询字段

    private $table = ''; // 当前操作的数据表

    private $joinStr    = ''; // 关联表查询
    private $joinOnStr  = ''; // 关联表查询的 闭包 ON 部分
    private $whereStr   = ''; // 查询条件
    private $groupByStr = ''; // 分组
    private $havingStr  = ''; // 过滤条件
    private $orderByStr = ''; // 排序
    private $limitStr   = ''; // 查询条数

    private $union = []; // 联合查询

    private $error = ''; // 异常信息

    private $config = []; // 连接配置信息


    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct($host, $dbname, $username, $password)
    {
        try {
            $this->config = compact('host', 'dbname', 'username', 'password');
            $this->pdo    = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("连接失败：" . $e->getMessage());
        }
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
     * @param $field
     * @param ...$args
     *
     * @return null
     */
    public function getOption($field = '', ...$args)
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
        // TODO 根据查询方式 判断使用 SELECT\INSERT\UPDATE\DELETE\REPLACE\TRUNCATE\CREATE\DROP\LOAD\ALTER\INDEX\SHOW\DO
        $this->query = "SELECT {$this->fieldStr} ";
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

    /**
     * 重新示例化
     *
     * @return $this
     */
    public function newQuery()
    {
        return new static(...$this->config);
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
                if ($conditions[2] instanceof Closure && is_string($conditions[1])) {
                    // 闭包函数
                    $fun      = $conditions[2];
                    $subClass = $this->newQuery();
                    $fun($subClass);

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
            $subClass = $this->newQuery();
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

    public function whereIn($field, $data)
    {
        $this->parseWhere('AND', $field, 'IN', $data);
        return $this;
    }

    public function whereNotIn($field, $data)
    {
        $this->parseWhere('AND', $field, 'NOT IN', $data);
        return $this;
    }

    /**
     * 添加 OR 条件
     */
    public function orWhere(...$conditions)
    {
        $this->parseWhere('OR', ...$conditions);
        return $this;
    }

    /**
     * 两个字段比较
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
     * OR 两个字段比较
     */
    public function orWhereColumn($first, $operator = '=', $second = null)
    {
        $this->whereColumn($first, $operator = '=', $second = null, $boolean = 'OR');
        return $this;
    }

    /**
     * 字段为空
     */
    public function whereNull($field)
    {
        $this->parseWhere('AND', $field, 'IS NULL', null);
        return $this;
    }

    /**
     * 字段不为空
     */
    public function whereNotNull($field)
    {
        $this->parseWhere('AND', $field, 'IS NOT NULL', null);
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
            // TODO 闭包函数
            $fun      = $conditions[1];
            $subClass = $this->newQuery();
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

    // 添加左连接查询
    public function leftJoin(...$joins)
    {
        $this->parseJoin('LEFT', ...$joins);
        return $this;
    }

    // 添加右连接查询
    public function rightJoin(...$joins)
    {
        $this->parseJoin('RIGHT', ...$joins);
        return $this;
    }

    // 添加全连接查询
    public function fullJoin($joins)
    {
        $this->parseJoin('FULL', ...$joins);
        return $this;
    }

    // 分组查询
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

    // 排序功能
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

    public function limit()
    {
        $this->limitStr = implode(', ', func_get_args());
        return $this;
    }

    /**
     * 执行查询
     */
    public function execute()
    {
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->parameters);
            $this->clear();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("数据库错误：" . $e->getMessage());
        }
    }

    /**
     * 获取所有结果
     */
    public function get()
    {
        $this->toSql();
        return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取第一条结果
     */
    public function first()
    {
        $this->toSql();
        return $this->execute()->fetch(PDO::FETCH_ASSOC);
    }

    // ==============================================

    // 添加防止 SQL 注入功能
    private function sanitize($value)
    {
        return $this->pdo->quote($value);
    }

    /**
     * 执行自定义查询
     */
    public function executeRawQuery($query, $parameters = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($parameters);
        return $stmt;
    }

    /**
     * 插入数据
     */
    public function insert($data)
    {
        $columns          = implode(', ', array_keys($data));
        $placeholders     = implode(', ', array_fill(0, count($data), '?'));
        $this->query      = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $this->parameters = array_values($data);
        return $this;
    }

    /**
     * 更新数据
     */
    public function update($data)
    {
        $set              = implode(' = ?, ', array_keys($data)) . ' = ?';
        $this->query      = "UPDATE {$this->table} SET $set";
        $this->parameters = array_values($data);
        return $this;
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $this->query = "DELETE FROM {$this->table}";
        return $this;
    }

    // 添加设置事务隔离级别的方法
    public function setTransactionIsolation($isolationLevel)
    {
        $this->pdo->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        return $this;
    }

    // 开启事务的方法
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
        return $this;
    }


    // 添加提交事务的方法
    public function commit()
    {
        $this->pdo->commit();
        return $this;
    }

    // 添加回滚事务的方法
    public function rollback()
    {
        $this->pdo->rollback();
        return $this;
    }

    /**
     * 执行事务
     */
    public function transaction($callback)
    {
        $this->pdo->beginTransaction();
        try {
            $callback($this);
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("事务回滚：" . $e->getMessage());
        }
    }

    // 添加获取单个值的聚合查询方法
    public function aggregate($function, $column)
    {
        $this->query = "SELECT $function($column) FROM {$this->table}";
        return $this->execute()->fetchColumn();
    }

    /**
     * 查询子查询条件中的 IN 查询
     */
    public function subQueryWhereIn($column, $subQuery)
    {
        $subQuery    = str_replace("SELECT", "SELECT $column FROM", $subQuery);
        $this->query .= " WHERE $column IN ($subQuery)";
        return $this;
    }

    // 添加子查询功能
    public function subQuery($subQuery, $alias)
    {
        $this->query = str_replace("*", "($subQuery) AS $alias", $this->query);
        return $this;
    }

    // 添加闭包查询功能
    public function closure($closure)
    {
        $this->query = call_user_func($closure, $this->query);
        return $this;
    }

    // 添加获取上一次插入的ID的方法
    public function getLastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 批量插入并返回插入的ID
     */
    public function batchInsertAndGetIds($table, $data)
    {
        $ids = [];
        foreach ($data as $row) {
            $this->insert($table, $row);
            $ids[] = $this->pdo->lastInsertId();
        }
        return $ids;
    }

    // 添加批量插入功能
    public function batchInsert($data)
    {
        foreach ($data as $row) {
            $this->insert($row)->execute();
        }
        return $this;
    }

    /**
     * 批量更新记录
     */
    public function batchUpdate($table, $data, $column)
    {
        $cases = [];
        foreach ($data as $key => $value) {
            $cases[] = "WHEN $column = $key THEN $value";
        }
        $cases = implode(' ', $cases);
        $sql   = "UPDATE $table SET $column = CASE $cases ELSE $column END";
        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // 添加异常处理功能
    public function catchError($callback)
    {
        try {
            $result = $callback();
            return $result;
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }



    // 添加构造器入口方法
    public function query()
    {
        return new self();
    }

// 添加更新自增操作
    public function increment($column, $amount = 1)
    {
        $this->query = "UPDATE {$this->table} SET $column = $column + $amount";
        return $this->execute();
    }

    // 添加更新自减操作
    public function decrement($column, $amount = 1)
    {
        return $this->increment($column, -$amount);
    }



    /**
     * 分页查询
     */
    // public function paginate($perPage, $currentPage = 1) {
    //     $this->limit($perPage)->offset(($currentPage - 1) * $perPage);
    //     return $this->get();
    // }

    // 添加分页查询的方法
    public function paginate($perPage, $currentPage = 1)
    {
        $offset      = ($currentPage - 1) * $perPage;
        $this->query .= " LIMIT $perPage OFFSET $offset";
        return $this->get();
    }

    // public function limit($perPage=0,$offset=10)
    // {
    //  $offset = ($currentPage - 1) * $perPage;
    //     $this->query .= " LIMIT $perPage OFFSET $offset";
    //  return $this;
    // }


    /**
     * 获取数据表字段信息
     */
    public function getTableColumns($table)
    {
        $stmt = $this->pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 添加获取字段列表的方法
    public function getColumns()
    {
        $stmt = $this->pdo->prepare("DESCRIBE {$this->table}");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
    }

    /**
     * 获取最后执行的查询语句
     */
    public function getLastQuery()
    {
        return $this->query;
    }

    /**
     * 获取最后执行的绑定参数
     */
    public function getLastParameters()
    {
        return $this->parameters;
    }


    // 添加遍历查询结果的方法
    public function each($callback)
    {
        $stmt = $this->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $callback($row);
        }
        return $this;
    }

    /**
     * 获取数据表索引信息
     */
    public function getTableIndexes($table)
    {
        $stmt = $this->pdo->prepare("SHOW INDEXES FROM $table");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 添加获取数据表外键信息的方法
    public function getForeignKeys()
    {
        $stmt = $this->pdo->prepare("SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '{$this->table}' AND CONSTRAINT_NAME != 'PRIMARY'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取结果数量
     */
    public function count()
    {
        $this->query = str_replace("*", "COUNT(*)", $this->query);
        return $this->execute()->fetchColumn();
    }

    // 添加取最大值的方法
    public function max($column)
    {
        return $this->aggregate('MAX', $column);
    }

    // 添加取最小值的方法
    public function min($column)
    {
        return $this->aggregate('MIN', $column);
    }

    // 添加取平均值的方法
    public function avg($column)
    {
        return $this->aggregate('AVG', $column);
    }

    // 添加取和的方法
    public function sum($column)
    {
        return $this->aggregate('SUM', $column);
    }

    // 添加连接多个条件的方法
    public function whereMany($conditions)
    {
        $this->query .= " WHERE ";
        $i           = 0;
        foreach ($conditions as $column => $value) {
            if ($i !== 0) {
                $this->query .= " AND ";
            }
            $this->query        .= "$column = ?";
            $this->parameters[] = $value;
            $i++;
        }
        return $this;
    }

    /**
     * 清除查询条件和参数
     */
    private function clear()
    {
        $this->query      = "";
        $this->parameters = [];
        return $this;
    }


    // 添加获取当前日期的方法
    public function currentDate()
    {
        return date('Y-m-d');
    }

    // 添加获取当前时间戳的方法
    public function currentTime()
    {
        return time();
    }

    /**
     * 获取数据表主键列名
     */
    public function getPrimaryKey($table)
    {
        $stmt = $this->pdo->prepare("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['Column_name'];
    }

    /**
     * 设置字符集
     */
    public function setCharset($charset)
    {
        $this->pdo->exec("SET NAMES $charset");
        return $this;
    }

}

// 使用继续来获取下一个片段

$db = new Database('127.0.0.1', 'test', 'root', '');
$db->table('fa_test', 'test');
$db->select('id, name')->select('age', 'num')->select(['age', 'num']);
$db->where('id', 1)->where(function ($query) {
    $query->where('name', 'admin');
})->orWhere(function ($query) {
    $query->where('sta', 'admin');
})->whereIn('a', ['a', 'b'])
    ->whereIn('b', function ($query) {
        $query->table('sub_table')->select('id')->where('sub_id', '<>', 9);
    })->whereColumn('time', '>', 'time1')
    ->whereNull('id_card')
    ->join('table_name AS t', 'test.id = t.table1_id')
    ->join('table_name1 AS t1', function ($join) {
        $join->on('join_1', 'join_2');
        $join->whereColumn('join_on_where_1', '=', 'join_on_where_2');
    })
    ->groupBy('id', 'name')
    ->having('id', '>', 1)
    ->orderBy('id', 'desc')
    ->orderBy(['name' => 'desc', 'age' => 'asc'])
    ->limit(10);

print_r($db->toSql());
$sql = $db->get();
print_r($sql);
print_r($db->getOption('parameters'));
