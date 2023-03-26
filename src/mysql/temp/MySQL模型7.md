使用PHP8通过mysqli实现一个对MySql的基础操作类，要求加上中文注释，包括创建、插入、删除、修改、查询、分组、排序、过滤、关联、关联查询、聚合查询、统计、关联统计、分页、游标分页、子查询、批量插入、批量更新、软删除、事务操作、锁表查询、预加载、分块查询、查询记录的功能

<?php

class MySqlBaseOperation {
    
    private $host = "localhost"; // 数据库主机名
    private $username = "username"; // 数据库用户名
    private $password = "password"; // 数据库密码
    private $dbname = "dbname"; // 数据库名
    private $port = "3306"; // 数据库端口号
    private $conn; // 数据库连接对象

    // 构造函数，用于创建数据库连接对象
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname, $this->port);
        if ($this->conn->connect_error) {
            die("连接失败: " . $this->conn->connect_error);
        }
    }

    // 创建表
    public function createTable($tableName, $fields) {
        $sql = "CREATE TABLE $tableName ($fields)";
        if ($this->conn->query($sql) === TRUE) {
            echo "表 $tableName 创建成功";
        } else {
            echo "创建表 $tableName 失败: " . $this->conn->error;
        }
    }

    // 插入数据
    public function insertData($tableName, $data) {
        $fields = implode(",", array_keys($data));
        $values = implode("','", array_values($data));
        $sql = "INSERT INTO $tableName ($fields) VALUES ('$values')";
        if ($this->conn->query($sql) === TRUE) {
            echo "数据插入成功";
        } else {
            echo "数据插入失败: " . $this->conn->error;
        }
    }

    // 删除数据
    public function deleteData($tableName, $condition) {
        $sql = "DELETE FROM $tableName WHERE $condition";
        if ($this->conn->query($sql) === TRUE) {
            echo "数据删除成功";
        } else {
            echo "数据删除失败: " . $this->conn->error;
        }
    }

    // 更新数据
    public function updateData($tableName, $data, $condition) {
        $values = "";
        foreach ($data as $key => $value) {
            $values .= "$key='$value',";
        }
        $values = rtrim($values, ",");
        $sql = "UPDATE $tableName SET $values WHERE $condition";
        if ($this->conn->query($sql) === TRUE) {
            echo "数据更新成功";
        } else {
            echo "数据更新失败: " . $this->conn->error;
        }
    }

    // 查询数据
    public function selectData($tableName, $fields = "*", $condition = "") {
        $sql = "SELECT $fields FROM $tableName";
        if ($condition != "") {
            $sql .= " WHERE $condition";
        }
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 分组查询
    public function groupBy($tableName, $fields, $groupBy) {
        $sql = "SELECT $fields FROM $tableName GROUP BY $groupBy";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 排序查询
    public function orderBy($tableName, $fields, $orderBy) {
        $sql = "SELECT $fields FROM $tableName ORDER BY $orderBy";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 过滤查询
    public function where($tableName, $fields, $condition) {
        $sql = "SELECT $fields FROM $tableName WHERE $condition";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 关联查询
    public function join($tableName1, $tableName2, $fields, $condition) {
        $sql = "SELECT $fields FROM $tableName1 JOIN $tableName2 ON $condition";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 聚合查询
    public function aggregate($tableName, $fields, $aggregate) {
        $sql = "SELECT $aggregate($fields) FROM $tableName";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 统计查询
    public function count($tableName, $fields) {
        $sql = "SELECT COUNT($fields) FROM $tableName";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 关联统计查询
    public function joinCount($tableName1, $tableName2, $fields, $condition) {
        $sql = "SELECT COUNT($fields) FROM $tableName1 JOIN $tableName2 ON $condition";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 分页查询
    public function limit($tableName, $fields, $start, $limit) {
        $sql = "SELECT $fields FROM $tableName LIMIT $start,$limit";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 游标分页查询
    public function cursorLimit($tableName, $fields, $cursor, $limit) {
        $sql = "SELECT $fields FROM $tableName WHERE id > $cursor LIMIT $limit";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 子查询
    public function subQuery($tableName, $fields, $subQuery) {
        $sql = "SELECT $fields FROM $tableName WHERE id IN ($subQuery)";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 批量插入数据
    public function insertBatch($tableName, $data) {
        $fields = implode(",", array_keys($data[0]));
        $values = "";
        foreach ($data as $item) {
            $values .= "('".implode("','", array_values($item))."'),";
        }
        $values = rtrim($values, ",");
        $sql = "INSERT INTO $tableName ($fields) VALUES $values";
        if ($this->conn->query($sql) === TRUE) {
            echo "数据插入成功";
        } else {
            echo "数据插入失败: " . $this->conn->error;
        }
    }

    // 批量更新数据
    public function updateBatch($tableName, $data, $primaryKey) {
        $sql = "UPDATE $tableName SET ";
        foreach ($data as $item) {
            $values = "";
            foreach ($item as $key => $value) {
                if ($key != $primaryKey) {
                    $values .= "$key='$value',";
                }
            }
            $values = rtrim($values, ",");
            $sql .= "$values WHERE $primaryKey=".$item[$primaryKey].";";
        }
        if ($this->conn->multi_query($sql) === TRUE) {
            echo "数据更新成功";
        } else {
            echo "数据更新失败: " . $this->conn->error;
        }
    }

    // 软删除数据
    public function softDeleteData($tableName, $condition) {
        $sql = "UPDATE $tableName SET is_deleted=1 WHERE $condition";
        if ($this->conn->query($sql) === TRUE) {
            echo "数据删除成功";
        } else {
            echo "数据删除失败: " . $this->conn->error;
        }
    }

    // 开启事务
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    // 提交事务
    public function commit() {
        $this->conn->commit();
    }

    // 回滚事务
    public function rollback() {
        $this->conn->rollback();
    }

    // 锁表查询
    public function lockTable($tableName) {
        $sql = "SELECT * FROM $tableName FOR UPDATE";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 预加载
    public function eagerLoading($tableName1, $tableName2, $fields, $condition) {
        $sql = "SELECT $fields FROM $tableName1 JOIN $tableName2 ON $condition";
        $result = $this->conn->query($sql);
        $data = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $result->close();
        $this->conn->next_result();
        return $data;
    }

    // 分块查询
    public function chunk($tableName, $fields, $callback, $size = 1000) {
        $sql = "SELECT $fields FROM $tableName";
        $result = $this->conn->query($sql);
        $offset = 0;
        while ($rows = $result->fetch_all(MYSQLI_ASSOC)) {
            $callback($rows);
            $offset += $size;
            $sql = "SELECT $fields FROM $tableName LIMIT $offset,$size";
            $result = $this->conn->query($sql);
        }
    }

    // 查询记录数
    public function countAll($tableName) {
        $sql = "SELECT COUNT(*) FROM $tableName";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            echo "查询结果为空";
        }
    }

    // 关闭数据库连接
    public function __destruct() {
        $this->conn->close();
    }
}