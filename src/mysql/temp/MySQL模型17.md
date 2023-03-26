如何使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，并封装和实现所有mysql8操作方法和接口类,包括条件聚合查询、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询、锁表更新的高级封装类功能

好的，我明白了你的要求。那么，你的问题是如何使用 PHP8 和 mysqli 实现一个操作 MySQL8 的高级封装类，要求加上中文注释，并封装和实现所有 MySQL8 操作方法和接口类，包括条件聚合查询、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询、锁表更新的高级封装类功能。我的回答是：

你可以使用以下代码来实现一个操作 MySQL8 的高级封装类：

<?php

class MySQLiDatabase {
    
    private $host;
    private $user;
    private $password;
    private $database;
    private $port;
    private $socket;
    private $charset;
    private $conn;
    
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
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket);
        if ($this->conn->connect_error) {
            throw new Exception('连接数据库失败: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset($this->charset);
    }
    
    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception('执行 SQL 语句失败: ' . $this->conn->error);
        }
        return $result;
    }
    
    public function select($table, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $sql = 'SELECT ' . $columns . ' FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        if ($order_by) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sql .= ' OFFSET ' . $offset;
        }
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(function ($value) {
            return "'" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data)));
        $sql = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ')';
        $result = $this->query($sql);
        return $result->insert_id;
    }
    
    public function update($table, $data, $where = null) {
        $set = implode(', ', array_map(function ($key, $value) {
            return $key . "='" . $this->conn->real_escape_string($value) . "'";
        }, array_keys($data), array_values($data)));
        $sql = 'UPDATE ' . $table . ' SET ' . $set;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->affected_rows;
    }
    
    public function delete($table, $where = null) {
        $sql = 'DELETE FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->affected_rows;
    }
    
    public function count($table, $where = null) {
        $sql = 'SELECT COUNT(*) AS count FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->fetch_assoc()['count'];
    }
    
    public function sum($table, $column, $where = null) {
        $sql = 'SELECT SUM(' . $column . ') AS sum FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->fetch_assoc()['sum'];
    }
    
    public function avg($table, $column, $where = null) {
        $sql = 'SELECT AVG(' . $column . ') AS avg FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->fetch_assoc()['avg'];
    }
    
    public function max($table, $column, $where = null) {
        $sql = 'SELECT MAX(' . $column . ') AS max FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->fetch_assoc()['max'];
    }
    
    public function min($table, $column, $where = null) {
        $sql = 'SELECT MIN(' . $column . ') AS min FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->query($sql);
        return $result->fetch_assoc()['min'];
    }
    
    public function join($table, $join_table, $join_column, $join_type = 'LEFT', $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $sql = 'SELECT ' . $columns . ' FROM ' . $table . ' ' . $join_type . ' JOIN ' . $join_table . ' ON ' . $table . '.' . $join_column . ' = ' . $join_table . '.' . $join_column;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        if ($order_by) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sql .= ' OFFSET ' . $offset;
        }
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function preload($table, $preload_table, $preload_column, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $preload_ids = $this->select($table, $preload_column);
        $preload_ids = array_column($preload_ids, $preload_column);
        $preload_ids = array_unique($preload_ids);
        $preload_ids = array_filter($preload_ids);
        $preload_ids = array_values($preload_ids);
        $preload_ids = implode(', ', $preload_ids);
        if (!$preload_ids) {
            return [];
        }
        $sql = 'SELECT ' . $columns . ' FROM ' . $preload_table . ' WHERE ' . $preload_column . ' IN (' . $preload_ids . ')';
        if ($where) {
            $sql .= ' AND ' . $where;
        }
        if ($order_by) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sql .= ' OFFSET ' . $offset;
        }
        $result = $this->query($sql);
        $preloaded_data = $result->fetch_all(MYSQLI_ASSOC);
        $preloaded_data = array_combine(array_column($preloaded_data, $preload_column), $preloaded_data);
        $data = $this->select($table, $columns);
        foreach ($data as &$row) {
            if (isset($preloaded_data[$row[$preload_column]])) {
                $row[$preload_table] = $preloaded_data[$row[$preload_column]];
            } else {
                $row[$preload_table] = null;
            }
        }
        return $data;
    }
    
    public function subquery($table, $subquery_table, $subquery_column, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $subquery_ids = $this->select($subquery_table, $subquery_column);
        $subquery_ids = array_column($subquery_ids, $subquery_column);
        $subquery_ids = array_unique($subquery_ids);
        $subquery_ids = array_filter($subquery_ids);
        $subquery_ids = array_values($subquery_ids);
        $subquery_ids = implode(', ', $subquery_ids);
        if (!$subquery_ids) {
            return [];
        }
        $sql = 'SELECT ' . $columns . ' FROM ' . $table . ' WHERE ' . $subquery_column . ' IN (' . $subquery_ids . ')';
        if ($where) {
            $sql .= ' AND ' . $where;
        }
        if ($order_by) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sql .= ' OFFSET ' . $offset;
        }
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function group($table, $group_column, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $sql = 'SELECT ' . $columns . ', COUNT(*) AS count FROM ' . $table;
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' GROUP BY ' . $group_column;
        if ($order_by) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sql .= ' OFFSET ' . $offset;
        }
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function filter($table, $filter_column, $filter_value, $columns = '*', $where = null, $order_by = null, $limit = null, $offset = null) {
        $sql = 'SELECT ' . $columns . ' FROM ' . $table . ' WHERE ' . $filter_column . ' = ' . $this->conn->real_escape_string($filter_value);
        if ($where) {
            $sql .= ' AND ' . $where;
        }
        if ($order_by) {