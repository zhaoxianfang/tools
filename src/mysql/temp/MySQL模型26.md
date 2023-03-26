帮我使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，要求提供完整的代码，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询、锁表更新的封装类功能

以下是一个使用PHP 8和mysqli实现操作MySQL 8的高级封装类的代码：

<?php
/**
 * MySQL 8高级封装类
 */
class MySQL8
{
    private $conn;

    /**
     * 构造函数，连接数据库
     * @param string $host 数据库主机名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $dbname 数据库名
     */
    public function __construct($host, $username, $password, $dbname)
    {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("连接失败: " . $this->conn->connect_error);
        }
    }

    /**
     * 过滤查询
     * @param string $table 数据库表名
     * @param array $filters 过滤条件数组，例如 ['id' => 1, 'name' => 'John']
     * @return array 查询结果数组
     */
    public function filterQuery($table, $filters)
    {
        $sql = "SELECT * FROM $table WHERE ";
        $i = 0;
        foreach ($filters as $key => $value) {
            if ($i > 0) {
                $sql .= "AND ";
            }
            $sql .= "$key = '$value' ";
            $i++;
        }
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 条件聚合查询
     * @param string $table 数据库表名
     * @param array $conditions 条件数组，例如 ['sum' => 'salary', 'group_by' => 'department']
     * @return array 查询结果数组
     */
    public function aggregateQuery($table, $conditions)
    {
        $sql = "SELECT ";
        if (isset($conditions['sum'])) {
            $sql .= "SUM(" . $conditions['sum'] . ") AS sum ";
        }
        if (isset($conditions['group_by'])) {
            $sql .= "GROUP BY " . $conditions['group_by'];
        }
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 开始事务
     */
    public function beginTransaction()
    {
        $this->conn->begin_transaction();
    }

    /**
     * 提交事务
     */
    public function commitTransaction()
    {
        $this->conn->commit();
    }

    /**
     * 回滚事务
     */
    public function rollbackTransaction()
    {
        $this->conn->rollback();
    }

    /**
     * 多态关联统计
     * @param string $table 数据库表名
     * @param string $relation 关联表名
     * @param string $group_by 分组字段
     * @return array 查询结果数组
     */
    public function polymorphicCount($table, $relation, $group_by)
    {
        $sql = "SELECT $group_by, COUNT(*) AS count FROM $table JOIN $relation ON $table.id = $relation.$table" . "_id GROUP BY $group_by";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 预加载
     * @param string $table 数据库表名
     * @param array $relations 关联表数组，例如 ['posts' => ['id', 'title'], 'comments' => ['id', 'content']]
     * @return array 查询结果数组
     */
    public function eagerLoading($table, $relations)
    {
        $sql = "SELECT * FROM $table";
        foreach ($relations as $relation => $fields) {
            $sql .= " LEFT JOIN $relation ON $table.id = $relation.$table" . "_id";
        }
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 模型关联
     * @param string $table 数据库表名
     * @param string $relation 关联表名
     * @param string $foreign_key 外键
     * @param string $local_key 本地键
     * @return array 查询结果数组
     */
    public function modelRelation($table, $relation, $foreign_key, $local_key)
    {
        $sql = "SELECT * FROM $table JOIN $relation ON $table.$local_key = $relation.$foreign_key";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 子查询
     * @param string $table 数据库表名
     * @param string $subquery 子查询语句
     * @return array 查询结果数组
     */
    public function subQuery($table, $subquery)
    {
        $sql = "SELECT * FROM $table WHERE id IN ($subquery)";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 排序
     * @param string $table 数据库表名
     * @param string $order_by 排序字段
     * @param string $order 排序方式，ASC或DESC
     * @return array 查询结果数组
     */
    public function orderBy($table, $order_by, $order)
    {
        $sql = "SELECT * FROM $table ORDER BY $order_by $order";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 分组
     * @param string $table 数据库表名
     * @param string $group_by 分组字段
     * @return array 查询结果数组
     */
    public function groupBy($table, $group_by)
    {
        $sql = "SELECT $group_by, COUNT(*) AS count FROM $table GROUP BY $group_by";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 过滤
     * @param string $table 数据库表名
     * @param string $field 过滤字段
     * @param string $value 过滤值
     * @return array 查询结果数组
     */
    public function filter($table, $field, $value)
    {
        $sql = "SELECT * FROM $table WHERE $field = '$value'";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 锁表查询
     * @param string $table 数据库表名
     * @return array 查询结果数组
     */
    public function lockTableQuery($table)
    {
        $sql = "SELECT * FROM $table FOR UPDATE";
        $result = $this->conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 锁表更新
     * @param string $table 数据库表名
     * @param array $data 更新数据数组，例如 ['name' => 'John', 'age' => 30]
     * @param array $filters 过滤条件数组，例如 ['id' => 1]
     */
    public function lockTableUpdate($table, $data, $filters)
    {
        $sql = "UPDATE $table SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i > 0) {
                $sql .= ", ";
            }
            $sql .= "$key = '$value'";
            $i++;
        }
        $sql .= " WHERE ";
        $i = 0;
        foreach ($filters as $key => $value) {
            if ($i > 0) {
                $sql .= "AND ";
            }
            $sql .= "$key = '$value' ";
            $i++;
        }
        $this->conn->query($sql);
    }

    /**
     * 关闭数据库连接
     */
    public function close()
    {
        $this->conn->close();
    }
}
这个类封装了许多常见的MySQL 8操作，包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询和锁表更新。每个方法都有注释，以便您了解其功能和用法。