完整使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能

<?php

class MySQLiDB {
    // 数据库连接实例
    private $connection;

    // 构造函数，接受连接参数并连接数据库
    public function __construct($host, $username, $password, $database) {
        $this->connection = new mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    // 析构函数，关闭连接
    public function __destruct() {
        $this->connection->close();
    }

    // 过滤查询方法
    public function select($table, $columns = "*", $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        return $this->query($query);
    }

    // 条件聚合查询方法
    public function aggregate($table, $columns, $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        return $this->query($query);
    }

    // 事务操作方法
    public function transaction($callback) {
        $this->connection->begin_transaction();

        try {
            call_user_func($callback, $this);
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    // 多态关联统计方法
    public function polymorphic($table, $id, $type) {
        $query = "SELECT COUNT(*) FROM $table WHERE id = $id AND type = '$type'";

        return $this->query($query);
    }

    // 预加载方法
    public function preload($table, $relations) {
        $query = "SELECT * FROM $table";

        foreach ($relations as $relation) {
            $query .= " LEFT JOIN $relation ON $table.id = $relation.$table"."_id";
        }

        return $this->query($query);
    }

    // 模型关联方法
    public function join($table1, $table2, $column1, $column2) {
        $query = "SELECT * FROM $table1 INNER JOIN $table2 ON $table1.$column1 = $table2.$column2";

        return $this->query($query);
    }

    // 子查询方法
    public function subquery($table, $subquery, $alias) {
        $query = "SELECT * FROM $table WHERE id IN ($subquery) AS $alias";

        return $this->query($query);
    }

    // 排序方法
    public function orderBy($table, $column, $direction = "ASC") {
        $query = "SELECT * FROM $table ORDER BY $column $direction";

        return $this->query($query);
    }

    // 分组方法
    public function groupBy($table, $column) {
        $query = "SELECT * FROM $table GROUP BY $column";

        return $this->query($query);
    }

    // 过滤方法
    public function where($table, $column, $operator, $value) {
        $query = "SELECT * FROM $table WHERE $column $operator $value";

        return $this->query($query);
    }

    // 带条件锁表查询方法
    public function selectForUpdate($table, $columns = "*", $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $query .= " FOR UPDATE";

        return $this->query($query);
    }

    // 带条件锁表更新方法
    public function updateForUpdate($table, $set, $where) {
        $query = "UPDATE $table SET $set WHERE $where";

        $query .= " FOR UPDATE";

        return $this->query($query);
    }

    // 执行查询方法
    private function query($query) {
        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->connection->error);
        }

        return $result;
    }
}


完整使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能






<?php

class MySQLiDB {
    // 数据库连接实例
    private $connection;

    // 构造函数，接受连接参数并连接数据库
    public function __construct($host, $username, $password, $database) {
        $this->connection = new mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    // 析构函数，关闭连接
    public function __destruct() {
        $this->connection->close();
    }

    // 过滤查询方法
    public function select($table, $columns = "*", $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        return $this->query($query);
    }

    // 条件聚合查询方法
    public function aggregate($table, $columns, $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        return $this->query($query);
    }

    // 事务操作方法
    public function transaction($callback) {
        $this->connection->begin_transaction();

        try {
            call_user_func($callback, $this);
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    // 多态关联统计方法
    public function polymorphic($table, $id, $type) {
        $query = "SELECT COUNT(*) FROM $table WHERE id = $id AND type = '$type'";

        return $this->query($query);
    }

    // 预加载方法
    public function preload($table, $relations) {
        $query = "SELECT * FROM $table";

        foreach ($relations as $relation) {
            $query .= " LEFT JOIN $relation ON $table.id = $relation.$table"."_id";
        }

        return $this->query($query);
    }

    // 模型关联方法
    public function join($table1, $table2, $column1, $column2) {
        $query = "SELECT * FROM $table1 INNER JOIN $table2 ON $table1.$column1 = $table2.$column2";

        return $this->query($query);
    }

    // 子查询方法
    public function subquery($table, $subquery, $alias) {
        $query = "SELECT * FROM $table WHERE id IN ($subquery) AS $alias";

        return $this->query($query);
    }

    // 排序方法
    public function orderBy($table, $column, $direction = "ASC") {
        $query = "SELECT * FROM $table ORDER BY $column $direction";

        return $this->query($query);
    }

    // 分组方法
    public function groupBy($table, $column) {
        $query = "SELECT * FROM $table GROUP BY $column";

        return $this->query($query);
    }

    // 过滤方法
    public function where($table, $column, $operator, $value) {
        $query = "SELECT * FROM $table WHERE $column $operator $value";

        return $this->query($query);
    }

    // 带条件锁表查询方法
    public function selectForUpdate($table, $columns = "*", $where = "") {
        $query = "SELECT $columns FROM $table";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        $query .= " FOR UPDATE";

        return $this->query($query);
    }

    // 带条件锁表更新方法
    public function updateForUpdate($table, $set, $where) {
        $query = "UPDATE $table SET $set WHERE $where";

        $query .= " FOR UPDATE";

        return $this->query($query);
    }

    // 执行查询方法
    private function query($query) {
        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->connection->error);
        }

        return $result;
    }
}




完整使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表查询、带条件锁表更新的封装类功能



