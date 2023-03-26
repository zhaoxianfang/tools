帮我使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能

<?php

class MySQLiDB {
    
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $charset;
    private $conn;
    
    public function __construct($host, $username, $password, $database, $port = 3306, $charset = 'utf8mb4') {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->charset = $charset;
        
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset($this->charset);
    }
    
    public function select($table, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $sql = "SELECT $columns FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        if ($order_by !== null) {
            $sql .= " ORDER BY $order_by";
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }
        
        if ($offset !== null) {
            $sql .= " OFFSET $offset";
        }
        
        return $this->query($sql);
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $values = implode(',', array_map(function ($value) {
            return "'" . $this->escape($value) . "'";
        }, array_values($data)));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        
        return $this->query($sql);
    }
    
    public function update($table, $data, $where = null) {
        $set = implode(',', array_map(function ($key, $value) {
            return "$key='" . $this->escape($value) . "'";
        }, array_keys($data), array_values($data)));
        
        $sql = "UPDATE $table SET $set";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        return $this->query($sql);
    }
    
    public function delete($table, $where = null) {
        $sql = "DELETE FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        return $this->query($sql);
    }
    
    public function query($sql) {
        $result = $this->conn->query($sql);
        
        if ($result === false) {
            die("Query failed: " . $this->conn->error);
        }
        
        return $result;
    }
    
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
    
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    public function commit() {
        $this->conn->commit();
    }
    
    public function rollback() {
        $this->conn->rollback();
    }
    
    public function count($table, $where = null) {
        $sql = "SELECT COUNT(*) FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->query($sql);
        
        return $result->fetch_row()[0];
    }
    
    public function sum($table, $column, $where = null) {
        $sql = "SELECT SUM($column) FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->query($sql);
        
        return $result->fetch_row()[0];
    }
    
    public function avg($table, $column, $where = null) {
        $sql = "SELECT AVG($column) FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->query($sql);
        
        return $result->fetch_row()[0];
    }
    
    public function min($table, $column, $where = null) {
        $sql = "SELECT MIN($column) FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->query($sql);
        
        return $result->fetch_row()[0];
    }
    
    public function max($table, $column, $where = null) {
        $sql = "SELECT MAX($column) FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->query($sql);
        
        return $result->fetch_row()[0];
    }
    
    public function hasOne($table, $foreign_key, $local_key) {
        $sql = "SELECT * FROM $table WHERE $foreign_key = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $local_key);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function hasMany($table, $foreign_key, $local_key) {
        $sql = "SELECT * FROM $table WHERE $foreign_key = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $local_key);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function belongsTo($table, $foreign_key, $local_key) {
        $sql = "SELECT * FROM $table WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $foreign_key);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function belongsToMany($table, $foreign_key, $local_key) {
        $sql = "SELECT * FROM $table WHERE $foreign_key IN (SELECT $foreign_key FROM $table WHERE $local_key = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $local_key);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function preload($table, $relation, $ids) {
        $sql = "SELECT * FROM $table WHERE id IN (SELECT $relation FROM $table WHERE id IN (" . implode(',', $ids) . "))";
        
        $result = $this->query($sql);
        
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!isset($data[$row['id']])) {
                $data[$row['id']] = $row;
                $data[$row['id']][$relation] = [];
            }
            
            $data[$row['id']][$relation][] = $row[$relation];
        }
        
        return $data;
    }
    
    public function subquery($table, $columns, $where = null) {
        $sql = "SELECT $columns FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        return "($sql)";
    }
    
    public function orderBy($columns, $direction = 'ASC') {
        return "ORDER BY $columns $direction";
    }
    
    public function groupBy($columns) {
        return "GROUP BY $columns";
    }
    
    public function where($column, $operator, $value) {
        return "$column $operator '$value'";
    }
    
    public function lockForUpdate() {
        return "FOR UPDATE";
    }
    
    public function lockForShare() {
        return "LOCK IN SHARE MODE";
    }
    
}