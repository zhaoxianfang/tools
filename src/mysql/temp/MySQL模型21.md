帮我使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，并封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询、锁表更新的封装类功能

编程
好的，我明白了你的要求。以下是一个使用PHP 8和MySQLi实现的高级封装类，包括所有MySQL 8的操作方法和接口类。我已经添加了中文注释，以便更好地理解代码。请注意，这只是一个示例，可能需要根据您的具体需求进行修改和调整。

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
    public function polymorphicCount($table, $column, $id, $type) {
        $sql = "SELECT COUNT(*) FROM " . $table . " WHERE " . $column . "_id = " . $id . " AND " . $column . "_type = '" . $type . "'";
        $result = $this->execute($sql);
        $row = $result->fetch_row();
        return $row[0];
    }
    
    // 预加载
    public function preload($table, $columns, $where = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        $result = $this->execute($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    // 模型关联
    public function modelJoin($table1, $table2, $join, $columns, $where = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table1 . " " . $join . " " . $table2;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        $result = $this->execute($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    // 子查询
    public function subQuery($table, $columns, $where = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table . " WHERE id IN (SELECT id FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        $sql .= ")";
        $result = $this->execute($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    // 排序
    public function orderBy($table, $columns, $order) {
        $sql = "SELECT * FROM " . $table . " ORDER BY " . implode(',', $columns) . " " . $order;
        return $this->getAll($sql);
    }
    
    // 分组
    public function groupBy($table, $columns, $having = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table . " GROUP BY " . implode(',', $columns);
        if (!empty($having)) {
            $sql .= " HAVING " . $having;
        }
        return $this->getAll($sql);
    }
    
    // 过滤
    public function filter($table, $columns, $where = '', $order = '', $limit = '') {
        $sql = "SELECT " . implode(',', $columns) . " FROM " . $table;
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        if (!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        return $this->getAll($sql);
    }
    
    // 锁表查询
    public function lockTableRead($table) {
        $this->execute("LOCK TABLES " . $table . " READ");
    }
    
    // 锁表更新
    public function lockTableWrite($table) {
        $this->execute("LOCK TABLES " . $table . " WRITE");
    }
    
    // 解锁表
    public function unlockTable() {
        $this->execute("UNLOCK TABLES");
    }
}

// 定义接口类
interface DatabaseInterface {
    public function connect();
    public function disconnect();
    public function escape($value);
    public function execute($sql);
    public function getOne($sql);
    public function getAll($sql);
    public function aggregate($table, $columns, $where = '', $group = '', $order = '', $limit = '');
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function polymorphicCount($table, $column, $id, $type);
    public function preload($table, $columns, $where = '', $order = '', $limit = '');
    public function modelJoin($table1, $table2, $join, $columns, $where = '', $order = '', $limit = '');
    public function subQuery($table, $columns, $where = '', $order = '', $limit = '');
    public function orderBy($table, $columns, $order);
    public function groupBy($table, $columns, $having = '');
    public function filter($table, $columns, $where = '', $order = '', $limit = '');
    public function lockTableRead($table);
    public function lockTableWrite($table);
    public function unlockTable();
}

?>
希望这个示例对你有所帮助。如果你有任何问题或需要进一步的帮助，请随时告诉我。