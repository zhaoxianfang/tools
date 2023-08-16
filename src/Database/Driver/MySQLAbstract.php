<?php

namespace zxf\Database\Driver;

use zxf\Database\Contracts\MysqlInterface;

abstract class MySQLAbstract implements MysqlInterface
{
    // mysql 连接信息
    protected $conn;
    // 最终查询的 SQL 语句
    protected $query; // 当前 SQL 查询
    // 查询的绑定参数
    protected $parameters = [];

    // pdo mysqli 等运行的查询实类
    protected $stmt;

    // 错误信息
    protected $error = '';
    // 表名
    protected $table = '';

    protected $config = []; // 连接配置信息

    protected $fieldStr   = ''; // 解析后的查询字段
    protected $joinStr    = ''; // 关联表查询
    protected $joinOnStr  = ''; // 关联表查询的 闭包 ON 部分
    protected $whereStr   = ''; // 查询条件
    protected $groupByStr = ''; // 分组
    protected $havingStr  = ''; // 过滤条件
    protected $orderByStr = ''; // 排序
    protected $limitStr   = ''; // 查询条数

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery()
    {
        return new static();
    }

    // 关闭连接
    public function close()
    {
        $this->conn->close();
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
                $func = $conditions[2];
                // 如果第伞个参数是闭包
                if (is_string($conditions[1]) && ($func instanceof Closure || is_callable($func))) {
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
        $this->whereColumn($first, $operator, $second, $boolean = 'OR');
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
        $func = $conditions[1];
        if ($func instanceof Closure || is_callable($func)) {
            $subClass = self::newQuery();
            $func($subClass);
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
     * 判断是否存在
     */
    public function exists()
    {
        $this->fieldStr = ' 1 ';
        return (bool)$this->first();
    }

    /**
     * 判断是否 不存在
     */
    public function doesntExist()
    {
        return !$this->exists();
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

    // 执行 一个SQL 语句
    public function query($sql = '')
    {
        return $this->conn->query($sql);
    }

    /**
     * 执行事务
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
            $this->rollBack();
            throw new Exception("事务回滚：" . $e->getMessage());
        }
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
}