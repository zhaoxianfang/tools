完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能

<?php

class MySQLiDB {
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $charset;
    private $conn;

    /**
     * 构造函数
     *
     * @param string $host     数据库主机名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $database 数据库名
     * @param int    $port     数据库端口号
     * @param string $charset  数据库字符集
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
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database, $this->port);

        if (!$this->conn) {
            die("连接数据库失败：" . mysqli_connect_error());
        }

        mysqli_set_charset($this->conn, $this->charset);
    }

    /**
     * 执行 SQL 语句
     *
     * @param string $sql SQL 语句
     *
     * @return mixed 执行结果
     */
    public function query($sql) {
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            die("执行 SQL 语句失败：" . mysqli_error($this->conn));
        }

        return $result;
    }

    /**
     * 过滤查询
     *
     * @param string $table 表名
     * @param array  $fields 字段数组
     * @param array  $params 参数数组
     *
     * @return array 查询结果数组
     */
    public function filter($table, $fields, $params) {
        $sql = "SELECT " . implode(",", $fields) . " FROM " . $table . " WHERE 1=1";

        foreach ($params as $key => $value) {
            $sql .= " AND " . $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
        }

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 条件不为空时闭包查询
     *
     * @param string   $table    表名
     * @param callable $callback 回调函数
     *
     * @return array 查询结果数组
     */
    public function where($table, $callback) {
        $sql = "SELECT * FROM " . $table . " WHERE 1=1";

        call_user_func($callback, $sql);

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 条件聚合查询
     *
     * @param string $table 表名
     * @param array  $params 参数数组
     *
     * @return array 查询结果数组
     */
    public function aggregate($table, $params) {
        $sql = "SELECT COUNT(*) AS total FROM " . $table . " WHERE 1=1";

        foreach ($params as $key => $value) {
            $sql .= " AND " . $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
        }

        $result = $this->query($sql);

        $row = mysqli_fetch_assoc($result);

        return $row['total'];
    }

    /**
     * 开始事务
     */mysqli_begin_transaction
    (public$this ->conn);
    }

    function beginTransaction() {
        /**
     * 提交事务
     */mysqli_commit
    (public$this ->conn);
    }

    function commit() {
        /**
     * 回滚事务
     */mysqli_rollback
    (public$this ->conn);
    }

    function rollback() {
        /**
     * 多态关联统计
     *
     * @param string $table      表名
     * @param string $foreign_id 外键 ID
     * @param string $foreign_table 外键表名
     *
     * @return array 查询结果数组
     */
    public function polymorphicCount($table, $foreign_id, $foreign_table) {
        $sql = "SELECT COUNT(*) AS total FROM " . $table . " WHERE foreign_id='" . mysqli_real_escape_string($this->conn, $foreign_id) . "' AND foreign_table='" . mysqli_real_escape_string($this->conn, $foreign_table) . "'";

        $result = $this->query($sql);

        $row = mysqli_fetch_assoc($result);

        return $row['total'];
    }

    /**
     * 预加载
     *
     * @param string $table     表名
     * @param array  $relations 关联数组
     *
     * @return array 查询结果数组
     */
    public function with($table, $relations) {
        $sql = "SELECT * FROM " . $table;

        foreach ($relations as $relation) {
            $sql .= " LEFT JOIN " . $relation['table'] . " ON " . $relation['foreign_key'] . "=" . $relation['local_key'];
        }

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 模型关联
     *
     * @param string $table     表名
     * @param array  $relations 关联数组
     *
     * @return array 查询结果数组
     */
    public function hasMany($table, $relations) {
        $sql = "SELECT * FROM " . $table;

        foreach ($relations as $relation) {
            $sql .= " LEFT JOIN " . $relation['table'] . " ON " . $table . "." . $relation['local_key'] . "=" . $relation['table'] . "." . $relation['foreign_key'];
        }

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 子查询
     *
     * @param string $table 表名
     * @param string $field 字段名
     * @param string $subquery 子查询语句
     *
     * @return array 查询结果数组
     */
    public function subquery($table, $field, $subquery) {
        $sql = "SELECT * FROM " . $table . " WHERE " . $field . " IN (" . $subquery . ")";

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 排序
     *
     * @param string $table 表名
     * @param string $field 字段名
     * @param string $order 排序方式
     *
     * @return array 查询结果数组
     */
    public function orderBy($table, $field, $order = 'ASC') {
        $sql = "SELECT * FROM " . $table . " ORDER BY " . $field . " " . $order;

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 分组
     *
     * @param string $table 表名
     * @param string $field 字段名
     *
     * @return array 查询结果数组
     */
    public function groupBy($table, $field) {
        $sql = "SELECT * FROM " . $table . " GROUP BY " . $field;

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 过滤
     *
     * @param string $table 表名
     * @param array  $params 参数数组
     *
     * @return array 查询结果数组
     */
    public function whereFilter($table, $params) {
        $sql = "SELECT * FROM " . $table . " WHERE 1=1";

        foreach ($params as $key => $value) {
            $sql .= " AND " . $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
        }

        $result = $this->query($sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 带条件锁表更新
     *
     * @param string $table 表名
     * @param array  $params 参数数组
     *
     * @return mixed 执行结果
     */
    public function updateWithLock($table, $params) {
        $sql = "UPDATE " . $table . " SET ";

        $set = [];
        foreach ($params['set'] as $key => $value) {
            $set[] = $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
        }

        $sql .= implode(",", $set);

        $sql .= " WHERE 1=1";

        foreach ($params['where'] as $key => $value) {
            $sql .= " AND " . $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
        }

        $sql .= " FOR UPDATE";

        return $this->query($sql);
    }

    /**
     * 关闭数据库连接
     */
    public function close() {
        mysqli_close($this->conn);
    }
}