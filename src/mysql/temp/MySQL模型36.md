<?php
/**
 * MySQL 8 高级封装类
 * 
 * 实现所有 MySQL 8 操作方法和接口类，包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能
 * 
 * @author 
 * @version 1.0
 */

class MySQLiDB {
    private $host; // 数据库主机名
    private $username; // 数据库用户名
    private $password; // 数据库密码
    private $database; // 数据库名
    private $port; // 数据库端口号
    private $charset; // 数据库字符集
    private $conn; // 数据库连接对象
    private $stmt; // 数据库语句对象
    private $result; // 数据库查询结果
    private $error; // 数据库错误信息

    /**
     * 构造函数
     * 
     * @param string $host 数据库主机名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $database 数据库名
     * @param int $port 数据库端口号
     * @param string $charset 数据库字符集
     */
    public function __construct($host, $username, $password, $database, $port = 3306, $charset = 'utf8mb4') {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->charset = $charset;
        $this->connect();
    }

    /**
     * 连接数据库
     */
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        if ($this->conn->connect_error) {
            $this->error = $this->conn->connect_error;
            return false;
        }
        $this->conn->set_charset($this->charset);
        return true;
    }

    /**
     * 关闭数据库连接
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    /**
     * 查询数据库
     * 
     * @param string $sql 查询语句
     * @param array $params 查询参数
     * @return array 查询结果
     */
    public function query($sql, $params = array()) {
        $this->stmt = $this->conn->prepare($sql);
        if (!$this->stmt) {
            $this->error = $this->conn->error;
            return false;
        }
        if (count($params) > 0) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
            $this->stmt->bind_param($types, ...$params);
        }
        if (!$this->stmt->execute()) {
            $this->error = $this->stmt->error;
            return false;
        }
        $this->result = $this->stmt->if (!$this->result) {
            get_result$this();
        ->error = $this->stmt->error;
            return false;
        }
        $rows = array();
        $this->result->while ($row = ;
        }
        fetch_assocreturn()) {
             $rows$rows[] = ;
    }

    $row/**
     * 获取最后插入的ID
     * 
     * @return int 最后插入的ID
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * 获取错误信息
     * 
     * @return string 错误信息
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 开始事务
     * 
     * @return bool 是否成功
     */
    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }

    /**
     * 提交事务
     * 
     * @return bool 是否成功
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * 回滚事务
     * 
     * @return bool 是否成功
     */
    public function rollback() {
        return $this->conn->rollback();
    }

    /**
     * 过滤查询
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @return array 查询结果
     */
    public function select($table, $params = array()) {
        $sql = "SELECT * FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        return $this->query($sql, array_values($params));
    }

    /**
     * 条件不为空时闭包查询
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param callable $callback 闭包函数
     * @return array 查询结果
     */
    public function when($table, $params, $callback) {
        if (count($params) > 0) {
            return call_user_func($callback, $this->select($table, $params));
        }
        return $this->select($table);
    }

    /**
     * 条件聚合查询
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param string $operator 聚合操作符
     * @return array 查询结果
     */
    public function aggregate($table, $params, $operator = 'COUNT') {
        $sql = "SELECT $operator(*) AS `count` FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        $result = $this->query($sql, array_values($params));
        return $result[0]['count'];
    }

    /**
     * 多态关联统计
     * 
     * @param string $table 表名
     * @param string $polymorphicType 多态类型
     * @param int $polymorphicId 多态ID
     * @param string $operator 聚合操作符
     * @return int 统计结果
     */
    public function polymorphicCount($table, $polymorphicType, $polymorphicId, $operator = 'COUNT') {
        $sql = "SELECT $operator(*) AS `count` FROM `$table` WHERE `polymorphic_type` = ? AND `polymorphic_id` = ?";
        $result = $this->query($sql, array($polymorphicType, $polymorphicId));
        return $result[0]['count'];
    }

    /**
     * 预加载
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param string $relation 关联表名
     * @param string $foreign_key 外键
     * @return array 查询结果
     */
    public function with($table, $params, $relation, $foreign_key) {
        $sql = "SELECT * FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        $result = $this->query($sql, array_values($params));
        $ids = array();
        foreach ($result as $row) {
            $ids[] = $row['id'];
        }
        $ids = implode(',', $ids);
        $sql = "SELECT * FROM `$relation` WHERE `$foreign_key` IN ($ids)";
        $relation_result = $this->query($sql);
        foreach ($result as &$row) {
            $row[$relation] = array();
            foreach ($relation_result as $relation_row) {
                if ($row['id'] == $relation_row[$foreign_key]) {
                    $row[$relation][] = $relation_row;
                }
            }
        }
        return $result;
    }

    /**
     * 模型关联
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param string $relation 关联表名
     * @param string $foreign_key 外键
     * @param string $local_key 本地键
     * @return array 查询结果
     */
    public function join($table, $params, $relation, $foreign_key, $local_key = 'id') {
        $sql = "SELECT * FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        $result = $this->query($sql, array_values($params));
        $ids = array();
        foreach ($result as $row) {
            $ids[] = $row[$local_key];
        }
        $ids = implode(',', $ids);
        $sql = "SELECT * FROM `$relation` WHERE `$foreign_key` IN ($ids)";
        $relation_result = $this->query($sql);
        foreach ($result as &$row) {
            $row[$relation] = array();
            foreach ($relation_result as $relation_row) {
                if ($row[$local_key] == $relation_row[$foreign_key]) {
                    $row[$relation][] = $relation_row;
                }
            }
        }
        return $result;
    }

    /**
     * 子查询
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param string $subquery 子查询语句
     * @param string $operator 操作符
     * @return array 查询结果
     */
    public function subquery($table, $params, $subquery, $operator = 'IN') {
        $sql = "SELECT * FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        $result = $this->query($sql, array_values($params));
        $ids = array();
        foreach ($result as $row) {
            $ids[] = $row['id'];
        }
        $ids = implode(',', $ids);
        $sql = "SELECT * FROM `$table` WHERE `id` $operator ($subquery)";
        return $this->query($sql, array($ids));
    }

    /**
     * 排序
     * 
     * @param string $table 表名
     * @param array $params 查询参数
     * @param string $column 排序列名
     * @param string $order 排序方式
     * @return array 查询结果
     */
    public function orderBy($table, $params, $column, $order = 'ASC') {
        $sql = "SELECT * FROM `$table` WHERE 1=1";
        foreach ($params as $key => $value) {
            $sql .= " AND `$key` = ?";
        }
        $sql .= " ORDER BY `$column` $order";
       
