
使用PHP8通过mysqli实现一个对MySql的基础操作类，要求加上中文注释，包括创建、插入、删除、修改、查询、分组、排序、过滤、关联、关联查询、聚合查询、统计、关联统计、分页、游标分页、子查询、批量插入、批量更新、软删除、事务操作、锁表查询、预加载、分块查询、类型转换的功能

<?php

/**
 * Class MySQLiHelper
 * 基于mysqli扩展的MySql操作类
 */
class MySQLiHelper
{
    protected $mysqli; // mysqli对象
    protected $table; // 表名
    protected $where; // where条件
    protected $order; // 排序
    protected $group; // 分组
    protected $limit; // 分页
    protected $fields; // 字段
    protected $join; // 关联表
    protected $softDelete; // 软删除标记
    protected $transaction; // 事务标记

    /**
     * MySQLiHelper constructor.
     * @param string $host 数据库主机地址
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $database 数据库名
     * @param string $charset 数据库字符集
     */
    public function __construct($host, $username, $password, $database, $charset = 'utf8')
    {
        // 创建mysqli对象
        $this->mysqli = new mysqli($host, $username, $password, $database);
        if ($this->mysqli->connect_errno) {
            die('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
        }
        // 设置字符集
        $this->mysqli->set_charset($charset);
    }

    /**
     * 设置表名
     * @param string $table 表名
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置where条件
     * @param array|string $where where条件
     * @return $this
     */
    public function where($where)
    {
        if (is_array($where)) {
            $whereArr = [];
            foreach ($where as $key => $value) {
                $whereArr[] = "`{$key}`='{$value}'";
            }
            $this->where = implode(' AND ', $whereArr);
        } else {
            $this->where = $where;
        }
        return $this;
    }

    /**
     * 设置排序
     * @param string $order 排序
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * 设置分组
     * @param string $group 分组
     * @return $this
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 设置分页
     * @param int $page 页码
     * @param int $pageSize 每页显示数量
     * @return $this
     */
    public function limit($page, $pageSize)
    {
        $this->limit = (($page - 1) * $pageSize) . ',' . $pageSize;
        return $this;
    }

    /**
     * 设置字段
     * @param string $fields 字段
     * @return $this
     */
    public function fields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 设置关联表
     * @param string $join 关联表
     * @return $this
     */
    public function join($join)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * 设置软删除标记
     * @param string $softDelete 软删除标记
     * @return $this
     */
    public function softDelete($softDelete)
    {
        $this->softDelete = $softDelete;
        return $this;
    }

    /**
     * 设置事务标记
     * @param bool $transaction 事务标记
     * @return $this
     */
    public function transaction($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * 获取单条记录
     * @return mixed|null
     */
    public function get()
    {
        $sql = "SELECT {$this->fields} FROM `{$this->table}`";
        if ($this->join) {
            $sql .= " {$this->join}";
        }
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->softDelete) {
            $sql .= " AND `{$this->softDelete}`=0";
        }
        $sql .= " LIMIT 1";
        $result = $this->query($sql);
        if ($result) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    /**
     * 获取多条记录
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT {$this->fields} FROM `{$this->table}`";
        if ($this->join) {
            $sql .= " {$this->join}";
        }
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->group) {
            $sql .= " GROUP BY {$this->group}";
        }
        if ($this->order) {
            $sql .= " ORDER BY {$this->order}";
        }
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->softDelete) {
            $sql .= " AND `{$this->softDelete}`=0";
        }
        $result = $this->query($sql);
        if ($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        } else {
            return [];
        }
    }

    /**
     * 插入记录
     * @param array $data 插入的数据
     * @return bool|int|string
     */
    public function insert($data)
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = "'" . $this->mysqli->real_escape_string($value) . "'";
        }
        $sql = "INSERT INTO `{$this->table}` (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        return $this->execute($sql);
    }

    /**
     * 更新记录
     * @param array $data 更新的数据
     * @return bool|int|string
     */
    public function update($data)
    {
        $setArr = [];
        foreach ($data as $key => $value) {
            $setArr[] = "`{$key}`='" . $this->mysqli->real_escape_string($value) . "'";
        }
        $sql = "UPDATE `{$this->table}` SET " . implode(',', $setArr);
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->softDelete) {
            $sql .= " AND `{$this->softDelete}`=0";
        }
        return $this->execute($sql);
    }

    /**
     * 删除记录
     * @return bool|int|string
     */
    public function delete()
    {
        $sql = "DELETE FROM `{$this->table}`";
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->softDelete) {
            $sql .= " AND `{$this->softDelete}`=0";
        }
        return $this->execute($sql);
    }

    /**
     * 统计记录数量
     * @return int
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) AS `count` FROM `{$this->table}`";
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->softDelete) {
            $sql .= " AND `{$this->softDelete}`=0";
        }
        $result = $this->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        } else {
            return 0;
        }
    }

    /**
     * 关联查询
     * @param string $table 关联表名
     * @param string $on 关联条件
     * @param string $fields 查询字段
     * @return $this
     */
    public function with($table, $on, $fields = '*')
    {
        $this->join = "LEFT JOIN `{$table}` ON {$on}";
        $this->fields = "{$this->table}.*,{$fields}";
        return $this;
    }

    /**
     * 关联统计
     * @param string $table 关联表名
     * @param string $on 关联条件
     * @param string $field 统计字段
     * @param string $alias 统计字段别名
     * @return $this
     */
    public function withCount($table, $on, $field, $alias = 'count')
    {
        $this->fields .= ",(SELECT COUNT(`{$field}`) FROM `{$table}` WHERE {$on}) AS `{$alias}`";
        return $this;
    }

    /**
     * 子查询
     * @param string $subQuery 子查询语句
     * @param string $alias 别名
     * @return $this
     */
    public function subQuery($subQuery, $alias)
    {
        $this->fields .= ",({$subQuery}) AS `{$alias}`";
        return $this;
    }

    /**
     * 批量插入
     * @param array $data 插入的数据
     * @return bool|int|string
     */
    public function batchInsert($data)
    {
        if (!is_array(reset($data))) {
            return false;
        }
        $fields = [];
        foreach (reset($data) as $key => $value) {
            $fields[] = "`{$key}`";
        }
        $valuesArr = [];
        foreach ($data as $item) {
            $values = [];
            foreach ($item as $value) {
                $values[] = "'" . $this->mysqli->real_escape_string($value) . "'";
            }
            $valuesArr[] = "(" . implode(',', $values) . ")";
        }
        $sql = "INSERT INTO `{$this->table}` (" . implode(',', $fields) . ") VALUES " . implode(',', $valuesArr);
        return $this->execute($sql);
    }

    /**
     * 批量更新
     * @param array $data 更新的数据
     * @return bool|int|string
     */
    public function batchUpdate($data)
    {
        if (!is_array(reset($data))) {
            return false;
        }
        $ids = [];
        $sqlArr = [];
        foreach ($data as $item) {
            $ids[] = $item['id'];
            $setArr = [];
            foreach ($item as $key => $value) {
                if ($key != 'id') {
                    $setArr[] = "`{$key}`='" . $this->mysqli->real_escape_string($value) . "'";
                }
            }
            $sqlArr[] = "UPDATE `{$this->table}` SET " . implode(',', $setArr) . " WHERE `id`=" . $item['id'];
        }
        $sql = implode(';', $sqlArr);
        if ($this->transaction) {
            $this->mysqli->autocommit(false);
            $this->mysqli->begin_transaction();
        }
        $result = $this->execute($sql);
        if ($this->transaction) {
            if ($result) {
                $this->mysqli->commit();
            } else {
                $this->mysqli->rollback();
            }
            $this->mysqli->autocommit(true);
        }
        return $result;
    }

    /**
     * 软删除
     * @param string $field 软删除标记字段
     * @param mixed $value 软删除标记值
     * @return $this
     */
    public function softDeleteBy($field, $value)
    {
        $this->softDelete = $field;
        $this->where("`{$field}`='{$value}'");
        return $this->update([$field => 1]);
    }

    /**
     * 开启事务
     * @return $this
     */
    public function beginTransaction()
    {
        $this