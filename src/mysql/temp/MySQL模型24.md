以下是完整的使用PHP 8和MySQLi实现的高级封装类，包括所有MySQL 8的操作方法和接口类。我已经添加了中文注释来帮助你理解每个方法的功能。

<?php

// 定义数据库连接信息
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'database');

class MySQLiDatabase {
    
    // 数据库连接
    private $connection;
    
    // 构造函数
    public function __construct() {
        $this->connect();
    }
    
    // 连接数据库
    public function connect() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            die("连接失败: " . $this->connection->connect_error);
        }
    }
    
    // 断开数据库连接
    public function disconnect() {
        $this->connection->close();
    }
    
    // 过滤查询
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    // 执行查询
    public function execute($sql) {
        return $this->connection->query($sql);
    }
    
    // 查询单个记录
    public function getOne($sql) {
        $result = $this->execute($sql);
        return $result->fetch_assoc();
    }
    
    // 查询多个记录
    public function getAll($sql) {
        $result = $this->execute($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    // 条件聚合查询
    public function aggregate($table, $columns, $where = '', $group = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($group)) {
            $sql .= " GROUP BY " . $group;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 开始事务
    public function beginTransaction() {
        $this->connection->begin_transaction();
    }
    
    // 提交事务
    public function commit() {
        $this->connection->commit();
    }
    
    // 回滚事务
    public function rollback() {
        $this->connection->rollback();
    }
    
    // 多态关联统计
    public function polymorphicCount($table, $id, $type) {
        $sql = "SELECT COUNT(*) FROM " . $table . " WHERE related_id = " . $id . " AND related_type = '" . $type . "'";
        $result = $this->execute($sql);
        $row = $result->fetch_row();
        return $row[0];
    }
    
    // 预加载
    public function preload($table, $column, $ids) {
        $sql = "SELECT * FROM " . $table . " WHERE " . $column . " IN (" . implode(',', $ids) . ")";
        return $this->getAll($sql);
    }
    
    // 模型关联
    public function modelJoin($table, $columns, $joinTable, $joinColumn, $where = '', $group = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table . " JOIN " . $joinTable . " ON " . $table . "." . $joinColumn . " = " . $joinTable . ".id";
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($group)) {
            $sql .= " GROUP BY " . $group;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 子查询
    public function subquery($table, $columns, $subquery, $where = '', $group = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table . " WHERE " . $subquery;
        if (!empty($where)) {
            $sql .= " AND " . $where;
        }
        if (!empty($group)) {
            $sql .= " GROUP BY " . $group;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 排序
    public function orderBy($table, $columns, $order = 'ASC', $limit = '') {
        $sql = "SELECT * FROM " . $table . " ORDER BY " . implode(',', $columns) . " " . $order;
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 分组
    public function groupBy($table, $columns, $where = '', $group = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($group)) {
            $sql .= " GROUP BY " . $group;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 过滤
    public function filter($table, $columns, $filters, $group = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table . " WHERE ";
        $conditions = array();
        foreach ($filters as $key => $value) {
            $conditions[] = $key . " = '" . $value . "'";
        }
        $sql .= implode

