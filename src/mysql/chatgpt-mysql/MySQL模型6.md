使用PHP8通过mysqli实现一个对MySql的基础操作类，要求加上中文注释，包括创建、插入、删除、修改、查询、分组、排序、过滤、关联、关联查询、聚合查询、统计、关联统计、分页、游标分页、子查询、批量插入、批量更新、软删除、事务操作、锁表查询、预加载、分块查询、执行记录的功能

<?php

class MySql {

    private $connection;

    // 构造函数，连接到数据库
    public function __construct($host, $username, $password, $database) {
        $this->connection = new mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            die("连接失败: " . $this->connection->connect_error);
        }
    }

    // 插入数据
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        return $this->connection->query($sql);
    }

    // 删除数据
    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->connection->query($sql);
    }

    // 更新数据
    public function update($table, $data, $where) {
        $set = "";
        foreach ($data as $key => $value) {
            $set .= "$key = '$value', ";
        }
        $set = rtrim($set, ", ");
        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->connection->query($sql);
    }

    // 查询数据
    public function select($table, $columns = "*", $where = null, $group_by = null, $order_by = null, $limit = null, $offset = null) {
        $sql = "SELECT $columns FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        if ($group_by) {
            $sql .= " GROUP BY $group_by";
        }
        if ($order_by) {
            $sql .= " ORDER BY $order_by";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }
        $result = $this->connection->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // 关联查询
    public function join($table1, $table2, $columns = "*", $on, $where = null, $group_by = null, $order_by = null, $limit = null, $offset = null) {
        $sql = "SELECT $columns FROM $table1 JOIN $table2 ON $on";
        if ($where) {
            $sql .= " WHERE $where";
        }
        if ($group_by) {
            $sql .= " GROUP BY $group_by";
        }
        if ($order_by) {
            $sql .= " ORDER BY $order_by";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }
        $result = $this->connection->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // 聚合查询
    public function aggregate($table, $function, $column, $where = null) {
        $sql = "SELECT $function($column) FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->connection->query($sql);
        return $result->fetch_assoc()[$function($column)];
    }

    // 关联统计
    public function joinCount($table1, $table2, $column, $on, $where = null) {
        $sql = "SELECT COUNT($column) FROM $table1 JOIN $table2 ON $on";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->connection->query($sql);
        return $result->fetch_assoc()["COUNT($column)"];
    }

    // 统计
    public function count($table, $column = "*") {
        $sql = "SELECT COUNT($column) FROM $table";
        $result = $this->connection->query($sql);
        return $result->fetch_assoc()["COUNT($column)"];
    }

    // 分页
    public function paginate($table, $columns = "*", $where = null, $group_by = null, $order_by = null, $per_page = 10, $page = 1) {
        $offset = ($page - 1) * $per_page;
        $limit = $per_page;
        return $this->select($table, $columns, $where, $group_by, $order_by, $limit, $offset);
    }

    // 游标分页
    public function cursorPaginate($table, $columns = "*", $where = null, $group_by = null, $order_by = null, $per_page = 10, $page = 1) {
        $offset = ($page - 1) * $per_page;
        $limit = $per_page;
        $sql = "SELECT COUNT(*) FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->connection->query($sql);
        $total = $result->fetch_assoc()["COUNT(*)"];
        $sql = "SELECT $columns FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        if ($group_by) {
            $sql .= " GROUP BY $group_by";
        }
        if ($order_by) {
            $sql .= " ORDER BY $order_by";
        }
        $sql .= " LIMIT $offset, $limit";
        $result = $this->connection->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return [
            "data" => $data,
            "current_page" => $page,
            "per_page" => $per_page,
            "total" => $total,
        ];
    }

    // 子查询
    public function subquery($table, $columns = "*", $where = null) {
        $sql = "SELECT $columns FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return "($sql)";
    }

    // 批量插入
    public function batchInsert($table, $data) {
        $columns = implode(", ", array_keys($data[0]));
        $values = [];
        foreach ($data as $row) {
            $values[] = "'" . implode("', '", array_values($row)) . "'";
        }
        $values = implode(", ", $values);
        $sql = "INSERT INTO $table ($columns) VALUES $values";
        return $this->connection->query($sql);
    }

    // 批量更新
    public function batchUpdate($table, $data, $where) {
        $set = "";
        foreach ($data as $row) {
            $set .= "(";
            foreach ($row as $key => $value) {
                $set .= "$key = '$value', ";
            }
            $set = rtrim($set, ", ");
            $set .= "), ";
        }
        $set = rtrim($set, ", ");
        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->connection->query($sql);
    }

    // 软删除
    public function softDelete($table, $where) {
        $sql = "UPDATE $table SET deleted_at = NOW() WHERE $where";
        return $this->connection->query($sql);
    }

    // 事务操作
    public function transaction($callback) {
        $this->connection->autocommit(false);
        try {
            $callback($this);
            $this->connection->commit();
            $this->connection->autocommit(true);
            return true;
        } catch (Exception $e) {
            $this->connection->rollback();
            $this->connection->autocommit(true);
            return false;
        }
    }

    // 锁表查询
    public function lock($table, $mode = "WRITE") {
        $sql = "LOCK TABLES $table $mode";
        return $this->connection->query($sql);
    }

    // 预加载
    public function with($table1, $table2, $foreign_key, $local_key = "id", $columns = "*", $where = null, $group_by = null, $order_by = null, $limit = null, $offset = null) {
        $sql = "SELECT $columns FROM $table1";
        if ($where) {
            $sql .= " WHERE $where";
        }
        if ($group_by) {
            $sql .= " GROUP BY $group_by";
        }
        if ($order_by) {
            $sql .= " ORDER BY $order_by";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }
        $result = $this->connection->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $sql = "SELECT $columns FROM $table2 WHERE $foreign_key = " . $row[$local_key];
            $result2 = $this->connection->query($sql);
            $row[$table2] = [];
            while ($row2 = $result2->fetch_assoc()) {
                $row[$table2][] = $row2;
            }
            $data[] = $row;
        }
        return $data;
    }

    // 分块查询
    public function chunk($table, $columns = "*", $where = null, $group_by = null, $order_by = null, $size = 100, $callback) {
        $page = 1;
        while (true) {
            $data = $this->paginate($table, $columns, $where, $group_by, $order_by, $size, $page);
            if (empty($data)) {
                break;
            }
            $callback($data);
            $page++;
        }
    }

    // 执行记录
    public function log($sql) {
        $file = fopen("sql.log", "a");
        fwrite($file, $sql . PHP_EOL);
        fclose($file);
    }

}