
<?php

/**
 * 高级封装类，用于操作MySQL8数据库
 */
class MySQL8 {

    private $host; // 数据库主机
    private $username; // 数据库用户名
    private $password; // 数据库密码
    private $database; // 数据库名称
    private $port; // 数据库端口
    private $charset; // 数据库字符集
    private $conn; // 数据库连接对象

    /**
     * 构造函数，初始化数据库连接
     * @param string $host 数据库主机
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $database 数据库名称
     * @param int $port 数据库端口，默认为3306
     * @param string $charset 数据库字符集，默认为utf8mb4
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
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        if ($this->conn->connect_error) {
            die('数据库连接失败：' . $this->conn->connect_error);
        }
        $this->conn->set_charset($this->charset);
    }

    /**
     * 关闭数据库连接
     */
    public function close() {
        $this->conn->close/**
     * 过滤查询条件
     * @param string $value 查询条件
     * @return string 过滤后的查询条件
     */
    public function filter($value) {
        return $this->conn->);
    }

    real_escape_string/**
     * 执行查询操作
     * @param string $sql 查询语句
     * @return array 查询结果数组
     */(
    $valuepublic function query($sql) {
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('查询失败：' . $this->conn->error);
        }
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return $rows;
    }

    /**
     * 执行插入操作
     * @param string $table 表名
     * @param array $data 插入数据数组
     * @return int 新插入的记录ID
     */
    public function insert($table, $data) {
        $keys = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = '`' . $key . '`'$values[] = "'" . $this->filter(;
            $value) . "'";
        }
        $sql = 'INSERT INTO `' . $table . '` (' . implode(',', $keys) . ') VALUES (' . implode(',', $values) . ')';
        if ($this->conn->query($sql) === false) {
            die('插入失败：' . $this->conn->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * 执行更新操作
     * @param string $table 表名
     * @param array $data 更新数据数组
     * @param string $where 更新条件
     * @return int 受影响的行数
     */
    public function update($table, $data, $where) {
        $sets = array();
        foreach ($data as $key => $value) {
            $sets[] = '`' . $key . '`=' . "'" . $this->filter($value) . "'";
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(',', $sets) . ' WHERE ' . $where;
        if ($this->conn->query($sql) === false) {
            die('更新失败：' . $this->conn->error);
        }
        return $this->conn->affected_rows;
    }

    /**
     * 执行删除操作
     * @param string $table 表名
     * @param string $where 删除条件
     * @return int 受影响的行数
     */
    public function delete($table, $where) {
        $sql = 'DELETE FROM `' . $table . '` WHERE ' . $where;
        if ($this->conn->query($sql) === false) {
            die('删除失败：' . $this->conn->error);
        }
        return $this->conn->affected_rows;
    }

    /**
     * 开始事务
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->conn->rollback();
    }

    ();
    }

    /**
     * 统计记录数
     * @param string $table 表名
     * @param string $where 查询条件
     * @return int 记录数
     */
    public function count($table, $where = '') {
        $sql = 'SELECT COUNT(*) AS count FROM `' . $table . '`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('查询失败：' . $this->conn->error);
        }
        $row = $result->fetch_assoc();
        $result->free();
        return $row['count'];
    }

    /**
     * 聚合查询
     * @param string $table 表名
     * @param string $field 聚合字段
     * @param string $where 查询条件
     * @param string $group 分组字段
     * @param string $having 分组条件
     * @return array 查询结果数组
     */
    public function aggregate($table, $field, $where = '', $group = '', $having = '') {
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        if ($group) {
            $sql .= ' GROUP BY ' . $group;
        }
        if ($having) {
            $sql .= ' HAVING ' . $having;
        }
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('查询失败：' . $this->conn->error);
        }
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return $rows;
    }

    /**
     * 多态关联统计
     * @param string $table 表名
     * @param string $field 统计字段
     * @param string $where 查询条件
     * @param string $group 分组字段
     * @param string $having 分组条件
     * @param string $join_table 关联表名
     * @param string $join_field 关联字段
     * @param string $join_type 关联类型，默认为INNER JOIN
     * @return array 查询结果数组
     */
    public function polymorphicCount($table, $field, $where = '', $group = '', $having = '', $join_table, $join_field, $join_type = 'INNER JOIN') {
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        if ($group) {
            $sql .= ' GROUP BY ' . $group;
        }
        if ($having) {
            $sql .= ' HAVING ' . $having;
        }
        $sql .= ' ' . $join_type . ' `' . $join_table . '` ON `' . $table . '`.`' . $join_field . '`=`' . $join_table . '`.`id`';
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('查询失败：' . $this->conn->error);
        }
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return $rows;
    }

    /**
     * 预加载关联数据
     * @param string $table 表名
     * @param string $field 查询字段
     * @param string $where 查询条件
     * @param string $join_table 关联表名
     * @param string $join_field 关联字段
     * @param string $join_type 关联类型，默认为LEFT JOIN
     * @return array 查询结果数组
     */
    public function eagerLoad($table, $field, $where = '', $join_table, $join_field, $join_type = 'LEFT JOIN') {
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '`';
        $sql .= ' ' . $join_type . ' `' . $join_table . '` ON `' . $table . '`.`' . $join_field . '`=`' . $join_table . '`.`id`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('查询失败：' . $this->conn->error);
        }
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return $rows;
    }

    /**
     * 模型关联查询
     * @param string $table 表名
     * @param string $field 查询字段
     * @param string $where 查询条件
     * @param string $join_table 关联表名
     * @param string $join_field 关联字段
     * @param string $join_type 关联类型，默认为LEFT JOIN
     * @param string $join_table2 第二个关联表名
     * @param string $join_field2 第二个关联字段
     * @param string $join_type2 第二个关联类型，默认为LEFT JOIN
     * @return array 查询结果数组
     */
    public function modelJoin($table, $field, $where = '', $join_table, $join_field, $join_type = 'LEFT JOIN', $join_table2 = '', $join_field2 = '', $join_type2 = 'LEFT JOIN') {
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '`';
        $sql .= ' ' . $join_type . ' `' . $join_table . '` ON `' . $table . '`.`' . $join_field . '`=`' . $join_table . '`.`id`';
        if ($join_table2 && $join_field2) {
            $sql .= ' ' . $join_type2 . ' `' . $join_table2 . '` ON `' . $table . '`.`' . $join_field2 . '`=`' . $join_table2 . '`.`id`';
        }
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $result = $this->conn->query($sql);
        if ($result === false) {