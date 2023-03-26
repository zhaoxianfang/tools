完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能

<?php

class MySQLiDB {
    private $host;
    private $user;
    private $password;
    private $database;
    private $port;
    private $charset;
    private $conn;
    private $query;
    private $result;

    public function __construct($host, $user, $password, $database, $port = 3306, $charset = 'utf8mb4') {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->charset = $charset;

        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);

        if ($this->conn->connect_error) {
            die('Connect Error (' . $this->conn->connect_errno . ') ' . $this->conn->connect_error);
        }

        $this->conn->set_charset($this->charset);
    }

    public function query($sql) {
        $this->query = $sql;
        $this->result = $this->conn->query($this->query);

        if (!$this->result) {
            die('Query Error (' . $this->conn->errno . ') ' . $this->conn->error);
        }

        return $this->result;
    }

    public function select($table, $columns = '*', $where = null, $limit = null, $offset = null, $orderBy = null, $groupBy = null) {
        $sql = "SELECT " . $columns . " FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        if ($orderBy != null) {
            $sql .= " ORDER BY " . $orderBy;
        }

        if ($groupBy != null) {
            $sql .= " GROUP BY " . $groupBy;
        }

        if ($limit != null) {
            $sql .= " LIMIT " . $limit;
        }

        if ($offset != null) {
            $sql .= " OFFSET " . $offset;
        }

        $this->query($sql);

        return $this->result;
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(function ($value) {
            return "'" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data)));

        $sql = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $values . ")";

        $this->query($sql);

        return $this->conn->insert_id;
    }

    public function update($table, $data, $where = null) {
        $set = implode(', ', array_map(function ($value, $key) {
            return $key . " = '" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data), array_keys($data)));

        $sql = "UPDATE " . $table . " SET " . $set;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        return $this->conn->affected_rows;
    }

    public function delete($table, $where = null) {
        $sql = "DELETE FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        return $this->conn->affected_rows;
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
        $sql = "SELECT COUNT(*) AS count FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        $row = $this->result->fetch_assoc();

        return $row['count'];
    }

    public function sum($table, $column, $where = null) {
        $sql = "SELECT SUM(" . $column . ") AS sum FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        $row = $this->result->fetch_assoc();

        return $row['sum'];
    }

    public function avg($table, $column, $where = null) {
        $sql = "SELECT AVG(" . $column . ") AS avg FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        $row = $this->result->fetch_assoc();

        return $row['avg'];
    }

    public function max($table, $column, $where = null) {
        $sql = "SELECT MAX(" . $column . ") AS max FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        $row = $this->result->fetch_assoc();

        return $row['max'];
    }

    public function min($table, $column, $where = null) {
        $sql = "SELECT MIN(" . $column . ") AS min FROM " . $table;

        if ($where != null) {
            $sql .= " WHERE " . $where;
        }

        $this->query($sql);

        $row = $this->result->fetch_assoc();

        return $row['min'];
    }

    public function hasOne($table, $foreignKey, $primaryKey) {
        return $this->select($table, '*', $foreignKey . " = " . $primaryKey, 1);
    }

    public function hasMany($table, $foreignKey, $primaryKey) {
        return $this->select($table, '*', $foreignKey . " = " . $primaryKey);
    }

    public function belongsTo($table, $foreignKey, $primaryKey) {
        return $this->select($table, '*', "id = " . $foreignKey, 1);
    }

    public function belongsToMany($table, $foreignKey, $primaryKey, $pivotTable, $pivotForeignKey, $pivotPrimaryKey) {
        $sql = "SELECT " . $table . ".* FROM " . $table . " INNER JOIN " . $pivotTable . " ON " . $table . ".id = " . $pivotTable . "." . $pivotForeignKey . " WHERE " . $pivotTable . "." . $pivotPrimaryKey . " = " . $primaryKey;

        return $this->query($sql);
    }

    public function preload($table, $foreignKey, $primaryKey, $relation) {
        $rows = $this->hasMany($table, $foreignKey, $primaryKey);

        foreach ($rows as $row) {
            $relatedRows = $this->{$relation['table']}->{$relation['method']}($relation['foreignKey'], $row['id']);
            $row[$relation['name']] = $relatedRows;
        }

        return $rows;
    }

    public function where($conditions) {
        $where = "";

        foreach ($conditions as $key => $value) {
            if ($where != "") {
                $where .= " AND ";
            }

            if (is_numeric($value)) {
                $where .= $key . " = " . $value;
            } else {
                $where .= $key . " = '" . $this->conn->real_escape_string($value) . "'";
            }
        }

        return $where;
    }

    public function like($conditions) {
        $like = "";

        foreach ($conditions as $key => $value) {
            if ($like != "") {
                $like .= " AND ";
            }

            $like .= $key . " LIKE '%" . $this->conn->real_escape_string($value) . "%'";
        }

        return $like;
    }

    public function orderBy($column, $direction = 'ASC') {
        return $column . " " . $direction;
    }

    public function groupBy($column) {
        return $column;
    }

    public function limit($limit, $offset = null) {
        if ($offset != null) {
            return $offset . ", " . $limit;
        }

        return $limit;
    }

    public function lockForUpdate() {
        return "FOR UPDATE";
    }

    public function lockInShareMode() {
        return "LOCK IN SHARE MODE";
    }
}