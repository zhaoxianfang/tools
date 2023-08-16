<?php

namespace zxf\Database;

use \Closure;
use \Exception;
use \PDO;
use \PDOException;

class Db
{
    private $stmt; // PDO 运行实例
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

    public $error = ''; // 异常信息

    private $config = []; // 连接配置信息


    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct()
    {
        $this->connect('default', ...func_get_args());
    }

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery()
    {
        return new static();
    }

    /**
     * 配置 连接数据库
     */
    public function connect($connectionName = 'default', ...$args)
    {
        if (!$this->getConfig($connectionName, ...$args)) {
            return false;
        }

        if (!extension_loaded('pdo')) {
            throw new Exception('不支持的扩展:pdo');
        }

        try {
            $pdoIc     = new \ReflectionClass('pdo');
            $this->pdo = $pdoIc->newInstanceArgs($this->config);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new Exception("Database 连接失败：" . $e->getMessage());
        }
        return $this;
    }

    private function getConfig($connectionName = 'default', ...$args)
    {
        if (empty($args) || !is_array($config = $args[0]) || count($config) < 4 || empty($config['host']) || empty($config['dbname']) || empty($config['username']) || !isset($config['password'])) {
            if (!function_exists('config') || empty($config = config('tools_database.mysql.' . $connectionName))) {
                return false;
            }
        }

        $dns          = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->config = [$dns, $config['username'], $config['password'] ?? ''];
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
     * 传入的$column值存在时才执行
     *
     * @param $column
     * @param $callback
     *
     * @return $this
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
            // TODO 闭包函数
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

    public function limit($offset = 0, $limit = 10)
    {
        $this->limitStr = $offset . ', ' . $limit;
        return $this;
    }

    /**
     * 执行查询
     */
    public function execute()
    {
        try {
            empty($this->query) && $this->toSql();
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->parameters);
            $this->stmt = $stmt;
            $this->clear();
            return $stmt;
        } catch (PDOException $e) {
            $this->clear();
            throw new Exception("数据库错误：" . $e->getMessage());
        }
    }

    /**
     * 获取所有结果
     */
    public function get()
    {
        return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取第一条结果
     */
    public function first()
    {
        return $this->execute()->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 判断是否存在
     */
    public function exists()
    {
        $this->fieldStr = ' 1 ';
        return $this->first() ? true : false;
    }

    /**
     * 判断是否 不存在
     */
    public function doesntExist()
    {
        return !$this->exists();
    }

    /**
     * 插入数据
     */
    public function insert($data)
    {
        try {
            $columns          = implode(', ', array_keys($data));
            $placeholders     = implode(', ', array_fill(0, count($data), '?'));
            $this->query      = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $this->parameters = array_values($data);
            $this->execute();
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 插入数据并获取ID
     */
    public function insertGetId($data)
    {
        $res = $this->insert($data);
        return $res ? $this->getLastInsertedId() : null;
    }

    /**
     * 获取上一次插入的ID
     *
     * @return false|string
     */
    public function getLastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 更新数据
     */
    public function update($data)
    {
        try {
            $set              = implode(' = ?, ', array_keys($data)) . ' = ?';
            $this->query      = "UPDATE {$this->table} SET $set";
            $this->query      .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $parameters       = array_values($data);
            $this->parameters = empty($this->parameters) ? $parameters : array_merge($parameters, $this->parameters);
            $stmt             = $this->execute();
            return $stmt->rowCount();

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 批量插入并返回插入的ID
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

    // 添加批量插入功能
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
     * 批量更新记录
     *
     * @param array  $data          被跟新的数据二维数组
     * @param string $byColumnField 根据$data中的哪个字段来更新
     *
     * @return false|int 返回影响的行数，false表示更新失败, int表示影响的行数
     *              返回false时，调用用 ->getError() 方法获取错误信息
     *
     * eg:  $updateData = [
     *          ['id' => 2, 'username' => 'username2-multi', 'nickname' => 'nickname2-multi'],
     *          ['id' => 3, 'username' => 'username3-multi', 'nickname' => 'nickname3-multi'],
     *          // 添加更多的更新数据项
     *      ];
     *      $db->table('test')->batchUpdate($updateData,'id');
     *
     * @throws Exception
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

            $stmt = $this->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    // 添加更新自增操作
    public function increment($column, $amount = 1)
    {
        $this->query = "UPDATE {$this->table} SET $column = $column + $amount";
        $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
        $stmt        = $this->execute();
        return $stmt->rowCount();
    }

    // 添加更新自减操作
    public function decrement($column, $amount = 1)
    {
        return $this->increment($column, -$amount);
    }

    /**
     * 删除数据
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
            $stmt        = $this->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    // 添加设置事务隔离级别的方法
    public function setTransactionIsolation($isolationLevel)
    {
        $this->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        return $this;
    }

    // 执行 SQL 语句，返回PDOStatement对象,可以理解为结果集
    public function queryDb($sql = '')
    {
        return $this->pdo->query($sql);
    }

    // 执行一条 SQL 语句，并返回受影响的行数
    public function exec($sql = '')
    {
        return $this->pdo->exec($sql);
    }

    // 添加防止 SQL 注入功能(为SQL语句中的字符串添加引号)
    public function quote($string)
    {
        return $this->pdo->quote($string);
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
        if (!is_callable($callback)) {
            throw new Exception("事务执行失败：参数必须是一个匿名函数");
        }

        $this->pdo->beginTransaction();
        try {
            $callback($this);
            $this->pdo->commit();
            $this->clear();
        } catch (Exception $e) {
            $this->clear();
            $this->pdo->rollBack();
            throw new Exception("事务回滚：" . $e->getMessage());
        }
    }

    // 检测是否在一个事务内
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * 清除查询条件和参数
     */
    public function clear()
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

        return $this;
    }

    // 添加闭包查询功能
    public function closure($closure)
    {
        $this->query = call_user_func($closure, $this->query);
        return $this;
    }

    // 添加分页查询的方法
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
        return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取最后执行的查询语句
     */
    public function getLastQuery()
    {
        // 获取最后一次查询的SQL语句
        return $this->stmt->queryString;
    }

    // 遍历查询结果的方法
    public function each($callback)
    {
        $stmt = $this->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $callback($row);
        }
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
        $primaryKeyRows   = $this->execute()->fetchAll(PDO::FETCH_ASSOC);
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
        $indexes          = $this->execute()->fetchAll(PDO::FETCH_ASSOC);

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
            return $this->execute()->fetchColumn();
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

    public function getParams()
    {
        //获取sql预处理命令
        return $this->stmt->debugDumpParams();
    }
}
