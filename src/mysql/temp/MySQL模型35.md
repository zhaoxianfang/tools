完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能

<?php

/**
 * MySQL8高级封装类
 *
 * 该类封装了所有MySQL8操作方法和接口类，包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新等功能。
 *
 * 作者：ChatGPT
 * 版本：1.0
 * 最后更新：2022年10月10日
 */

class MySQLiAdvanced
{
    /**
     * 数据库连接对象
     *
     * @var mysqli
     */
    private $connection;

    /**
     * 构造函数
     *
     * @param string $host 数据库主机名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $database 数据库名称
     * @param int $port 数据库端口号
     */
    public function __construct(string $host, string $username, string $password, string $database, int $port = 3306)
    {
        $this->connection = new mysqli($host, $username, $password, $database, $port);

        if ($this->connection->connect_error) {
            die("连接数据库失败：" . $this->connection->connect_error);
        }

        // 设置字符集为UTF-8
        $this->connection->set_charset("utf8mb4");
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * 开始事务
     */
    public function beginTransaction()
    {
        $this->connection->begin_transaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        $this->connection->rollback();
    }

    /**
     * 执行SQL查询语句
     *
     * @param string $sql SQL查询语句
     * @param array $params 参数列表
     * @return array 结果集
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    /**
     * 执行SQL更新语句
     *
     * @param string $sql SQL更新语句
     * @param array $params 参数列表
     * @return int 受影响的行数
     */
    public function update(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows;
    }

    /**
     * 执行SQL插入语句
     *
     * @param string $sql SQL插入语句
     * @param array $params 参数列表
     * @return int 新插入的ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    }

    /**
     * 执行SQL删除语句
     *
     * @param string $sql SQL删除语句
     * @param array $params 参数列表
     * @return int 受影响的行数
     */
    public function delete(string $sql, array $params = []): int
    {
        return $this->update($sql, $params);
    }

    /**
     * 准备SQL语句
     *
     * @param string $sql SQL语句
     * @param array $params 参数列表
     * @return mysqli_stmt 准备好的语句对象
     */
    private function prepare(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);

        if (count($params) > 0) {
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }

        return $stmt;
    }

    /**
     * 过滤查询
     *
     * @param string $table 表名
     * @param array $filter 过滤条件
     * @param array $fields 返回的字段列表
     * @return array 结果集
     */
    public function filterQuery(string $table, array $filter, array $fields = ['*']): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " WHERE ";

        foreach ($filter as $key => $value) {
            $sql .= $key . " = ? AND ";
        }

        $sql = rtrim($sql, "AND ");
        return $this->query($sql, array_values($filter));
    }

    /**
     * 条件不为空时闭包查询
     *
     * @param string $table 表名
     * @param array $conditions 查询条件
     * @param array $fields 返回的字段列表
     * @param callable $callback 闭包函数
     * @return array 结果集
     */
    public function closureQuery(string $table, array $conditions, array $fields = ['*'], callable $callback): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " WHERE ";

        foreach ($conditions as $key => $value) {
            $sql .= $key . " = ? AND ";
        }

        $sql = rtrim($sql, "AND ");

        if (!empty($conditions)) {
            $sql .= "AND ";
        }

        $sql .= "1 = 1";

        return $callback($this->query($sql, array_values($conditions)));
    }

    /**
     * 条件聚合查询
     *
     * @param string $table 表名
     * @param array $conditions 查询条件
     * @param array $fields 返回的字段列表
     * @param string $operator 聚合操作符
     * @return mixed 聚合结果
     */
    public function aggregateQuery(string $table, array $conditions, array $fields, string $operator)
    {
        $sql = "SELECT " . $operator . "(" . implode(", ", $fields) . ") AS result FROM " . $table . " WHERE ";

        foreach ($conditions as $key => $value) {
            $sql .= $key . " = ? AND ";
        }

        $sql = rtrim($sql, "AND ");
        $result = $this->query($sql, array_values($conditions));
        return $result[0]['result'];
    }

    /**
     * 多态关联统计
     *
     * @param string $table 表名
     * @param string $relation 关联表名
     * @param string $foreign_key 外键字段名
     * @param string $type 多态类型
     * @param int $id 外键ID
     * @return int 统计结果
     */
    public function polymorphicCount(string $table, string $relation, string $foreign_key, string $type, int $id): int
    {
        $sql = "SELECT COUNT(*) AS count FROM " . $table . " JOIN " . $relation . " ON " . $table . ".id = " . $relation . ".record_id WHERE " . $relation . ".record_type = ? AND " . $relation . "." . $foreign_key . " = ?";
        $result = $this->query($sql, [$type, $id]);
        return $result[0]['count'];
    }

    /**
     * 预加载
     *
     * @param string $table 表名
     * @param array $ids ID列表
     * @param string $foreign_key 外键字段名
     * @param array $fields 返回的字段列表
     * @return array 结果集
     */
    public function eagerLoad(string $table, array $ids, string $foreign_key, array $fields = ['*']): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " WHERE " . $foreign_key . " IN (" . implode(",", array_fill(0, count($ids), "?")) . ")";
        return $this->query($sql, $ids);
    }

    /**
     * 模型关联
     *
     * @param string $table 表名
     * @param string $relation 关联表名
     * @param string $foreign_key 外键字段名
     * @param string $local_key 本地字段名
     * @param int $id 外键ID
     * @param array $fields 返回的字段列表
     * @return array 结果集
     */
    public function modelJoin(string $table, string $relation, string $foreign_key, string $local_key, int $id, array $fields = ['*']): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " JOIN " . $relation . " ON " . $table . "." . $local_key . " = " . $relation . "." . $foreign_key . " WHERE " . $relation . "." . $foreign_key . " = ?";
        return $this->query($sql, [$id]);
    }

    /**
     * 子查询
     *
     * @param string $table 表名
     * @param array $fields 返回的字段列表
     * @param string $subquery 子查询语句
     * @param array $params 参数列表
     * @return array 结果集
     */
    public function subQuery(string $table, array $fields = ['*'], string $subquery, array $params = []): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " WHERE id IN (" . $subquery . ")";
        return $this->query($sql, $params);
    }

    /**
     * 排序
     *
     * @param string $table 表名
     * @param string $field 排序字段
     * @param string $direction 排序方向
     * @param array $fields 返回的字段列表
     * @return array 结果集
     */
    public function orderBy(string $table, string $field, string $direction = 'ASC', array $fields = ['*']): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " ORDER BY " . $field . " " . $direction;
        return $this->query($sql);
    }

    /**
     * 分组
     *
     * @param string $table 表名
     * @param string $field 分组字段
     * @param array $fields 返回的字段列表
     * @return array 结果集
     */
    public function groupBy(string $table, string $field, array $fields = ['*']): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " GROUP BY " . $field;
        return $this->query($sql);
    }

    /**
     * 过滤
     *
     * @param string $table 表名
     * @param array $fields 返回的字段列表
     * @param array $filter 过滤条件
     * @return array 结果集
     */
    public function filter(string $table, array $fields = ['*'], array $filter): array
    {
        $sql = "SELECT " . implode(", ", $fields) . " FROM " . $table . " WHERE ";

        foreach ($filter as $key => $value) {
            $sql .= $key . " =