完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能

<?php

class MySQLiDB {
    
    private $host;
    private $user;
    private $password;
    private $database;
    private $port;
    private $socket;
    private $charset;
    private $conn;
    private $query;
    private $result;
    
    public function __construct($host, $user, $password, $database, $port = null, $socket = null, $charset = 'utf8mb4') {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->socket = $socket;
        $this->charset = $charset;
        $this->connect();
    }
    
    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket);
        if ($this->conn->connect_error) {
            die("连接失败: " . $this->conn->connect_error);
        }
        $this->conn->set_charset($this->charset);
    }
    
    public function query($sql) {
        $this->query = $sql;
        $this->result = $this->conn->query($this->query);
        if (!$this->result) {
            die("查询失败: " . $this->conn->error);
        }
        return $this->result;
    }
    
    public function filter($value) {
        return $this->conn->real_escape_string($value);
    }
    
    public function where($column, $operator, $value) {
        $value = $this->filter($value);
        $this->query .= " WHERE $column $operator '$value'";
        return $this;
    }
    
    public function orWhere($column, $operator, $value) {
        $value = $this->filter($value);
        $this->query .= " OR $column $operator '$value'";
        return $this;
    }
    
    public function when($condition, $callback) {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }
    
    public function groupBy($column) {
        $this->query .= " GROUP BY $column";
        return $this;
    }
    
    public function orderBy($column, $direction = 'ASC') {
        $this->query .= " ORDER BY $column $direction";
        return $this;
    }
    
    public function limit($offset, $limit) {
        $this->query .= " LIMIT $offset, $limit";
        return $this;
    }
    
    public function get($table) {
        $this->query = "SELECT * FROM $table";
        return $this->query($this->query);
    }
    
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = implode("', '", array_map(array($this, 'filter'), array_values($data)));
        $this->query = "INSERT INTO $table ($columns) VALUES ('$values')";
        return $this->query($this->query);
    }
    
    public function update($table, $data) {
        $set = implode(", ", array_map(function($key, $value) {
            $value = $this->filter($value);
            return "$key = '$value'";
        }, array_keys($data), array_values($data)));
        $this->query = "UPDATE $table SET $set";
        return $this;
    }
    
    public function delete($table) {
        $this->query = "DELETE FROM $table";
        return $this;
    }
    
    public function count($column = '*') {
        $this->query = "SELECT COUNT($column) FROM ($this->query) AS count";
        $result = $this->query($this->query)->fetch_array();
        return $result[0];
    }
    
    public function sum($column) {
        $this->query = "SELECT SUM($column) FROM ($this->query) AS sum";
        $result = $this->query($this->query)->fetch_array();
        return $result[0];
    }
    
    public function avg($column) {
        $this->query = "SELECT AVG($column) FROM ($this->query) AS avg";
        $result = $this->query($this->query)->fetch_array();
        return $result[0];
    }
    
    public function min($column) {
        $this->query = "SELECT MIN($column) FROM ($this->query) AS min";
        $result = $this->query($this->query)->fetch_array();
        return $result[0];
    }
    
    public function max($column) {
        $this->query = "SELECT MAX($column) FROM ($this->query) AS max";
        $result = $this->query($this->query)->fetch_array();
        return $result[0];
    }
    
    public function transaction($callback) {
        $this->conn->begin_transaction();
        try {
            $callback($this);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
        }
    }
    
    public function preload($table, $foreign_key, $local_key) {
        $ids = array();
        $results = array();
        while ($row = $this->result->fetch_assoc()) {
            $ids[] = $row[$local_key];
            $results[] = $row;
        }
        $ids = array_unique($ids);
        if (count($ids) > 0) {
            $in = implode(", ", array_map(array($this, 'filter'), $ids));
            $query = "SELECT * FROM $table WHERE $foreign_key IN ($in)";
            $result = $this->query($query);
            $related = array();
            while ($row = $result->fetch_assoc()) {
                $related[$row[]][] = $foreign_key$row;
            }
            foreach ($results as &$result) {
                $result[] = $related$table[$result[$local_key]];
            }
        }
        $this->result->data_seek(0);
        return $this;
    }
    
    public function join($table, $foreign_key, $local_key) {
        $this->query .= " JOIN $table ON $foreign_key = $local_key";
        return $this;
    }
    
    public function leftJoin($table, $foreign_key, $local_key) {
        $this->query .= " LEFT JOIN $table ON $foreign_key = $local_key";
        return $this;
    }
    
    public function rightJoin($table, $foreign_key, $local_key) {
        $this->query .= " RIGHT JOIN $table ON $foreign_key = $local_key";
        return $this;
    }
    
    public function subQuery($subQuery, $alias) {
        $this->query .= " ($subQuery) AS $alias";
        return $this;
    }
    
    public function lockForUpdate() {
        $this->query .= " FOR UPDATE";
        return $this;
    }
    
    public function sharedLock() {
        $this->query .= " LOCK IN SHARE MODE";
        return $this;
    }
    
    public function execute() {
        return $this->query($this->query);
    }
    
    public function fetch() {
        return $this->result->fetch_assoc();
    }
    
    public function fetchAll() {
        $rows = array();
        while ($row = $this->result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function close() {
        $this->conn->close();
    }
    
}