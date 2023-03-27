完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能



<?php

class MySQLiDB {

    private $conn;

    public function __construct($host, $user, $password, $database) {
        $this->conn = new mysqli($host, $user, $password, $database);

        if ($this->conn->connect_error) {
            die("连接失败: " . $this->conn->connect_error);
        }
    }

    public function __destruct() {
        $this->conn->close();
    }

    public function select($table, $columns = "*", $where = "", $limit = "", $offset = "", $orderBy = "", $groupBy = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        if ($groupBy != "") {
            $query .= " GROUP BY $groupBy";
        }

        if ($orderBy != "") {
            $query .= " ORDER BY $orderBy";
        }

        if ($limit != "") {
            $query .= " LIMIT $limit";
        }

        if ($offset != "") {
            $query .= " OFFSET $offset";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);

        $query = "INSERT INTO $table (" . implode(", ", $keys) . ") VALUES ('" . implode("', '", $values) . "')";

        $result = $this->conn->query($query);

        if (!$result) {
            die("插入失败: " . $this->conn->error);
        }

        return $this->conn->insert_id;
    }

    public function update($table, $data, $where = "") {
        $sets = [];

        foreach ($data as $key => $value) {
            $sets[] = "$key = '$value'";
        }

        $query = "UPDATE $table SET " . implode(", ", $sets);

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("更新失败: " . $this->conn->error);
        }

        return $this->conn->affected_rows;
    }

    public function delete($table, $where = "") {
        $query = "DELETE FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("删除失败: " . $this->conn->error);
        }

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

    public function count($table, $where = "") {
        $query = "SELECT COUNT(*) AS count FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc()['count'];
    }

    public function sum($table, $column, $where = "") {
        $query = "SELECT SUM($column) AS sum FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc()['sum'];
    }

    public function avg($table, $column, $where = "") {
        $query = "SELECT AVG($column) AS avg FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc()['avg'];
    }

    public function max($table, $column, $where = "") {
        $query = "SELECT MAX($column) AS max FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc()['max'];
    }

    public function min($table, $column, $where = "") {
        $query = "SELECT MIN($column) AS min FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc()['min'];
    }

    public function hasMany($table, $foreign_key, $where = "") {
        $query = "SELECT * FROM $table WHERE $foreign_key = ?";

        if ($where != "") {
            $query .= " AND $where";
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("预处理失败: " . $this->conn->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function belongsTo($table, $foreign_key, $where = "") {
        $query = "SELECT * FROM $table WHERE id = (SELECT $foreign_key FROM " . get_class($this) . " WHERE id = ?)";

        if ($where != "") {
            $query .= " AND $where";
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("预处理失败: " . $this->conn->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }

    public function hasOne($table, $foreign_key, $where = "") {
        $query = "SELECT * FROM $table WHERE $foreign_key = ?";

        if ($where != "") {
            $query .= " AND $where";
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("预处理失败: " . $this->conn->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }

    public function hasManyThrough($table, $through_table, $foreign_key, $through_foreign_key, $where = "") {
        $query = "SELECT $table.* FROM $table JOIN $through_table ON $table.id = $through_table.$foreign_key WHERE $through_table.$through_foreign_key = ?";

        if ($where != "") {
            $query .= " AND $where";
        }

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("预处理失败: " . $this->conn->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function preload($table, $foreign_key, $where = "") {
        $query = "SELECT * FROM $table WHERE $foreign_key IN (?)";

        if ($where != "") {
            $query .= " AND $where";
        }

        $ids = array_column($this->hasMany($table, $foreign_key), 'id');

        if (count($ids) == 0) {
            return [];
        }

        $stmt = $this->conn->prepare(str_replace("?", implode(",", array_fill(0, count($ids), "?")), $query));

        if (!$stmt) {
            die("预处理失败: " . $this->conn->error);
        }

        $stmt->bind_param(str_repeat("i", count($ids)), ...$ids);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function subquery($table, $columns = "*", $where = "", $limit = "", $offset = "", $orderBy = "", $groupBy = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        if ($groupBy != "") {
            $query .= " GROUP BY $groupBy";
        }

        if ($orderBy != "") {
            $query .= " ORDER BY $orderBy";
        }

        if ($limit != "") {
            $query .= " LIMIT $limit";
        }

        if ($offset != "") {
            $query .= " OFFSET $offset";
        }

        return "($query)";
    }

    public function lockForUpdate($table, $where = "") {
        $query = "SELECT * FROM $table WHERE 1 = 1";

        if ($where != "") {
            $query .= " AND $where";
        }

        $query .= " FOR UPDATE";

        $result = $this->conn->query($query);

        if (!$result) {
            die("查询失败: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function lockForUpdateUpdate($table, $data, $where = "") {
        $this->conn->autocommit(FALSE);

        $sets = [];

        foreach ($data as $key => $value) {
            $sets[] = "$key = '$value'";
        }

        $query = "UPDATE $table SET " . implode(", ", $sets);

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $query .= " FOR UPDATE";

        $result = $this->conn->query($query);

        if (!$result) {
            $this->conn->rollback();
            die("更新失败: " . $this->conn->error);
        }

        $this->conn->commit();

        return $this->conn->affected_rows;
    }

}