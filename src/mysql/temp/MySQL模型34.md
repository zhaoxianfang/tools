完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能

<?php

/**
 * Class MySQLiDB
 *
 * 一个使用mysqli扩展操作MySQL 8的高级封装类
 */
class MySQLiDB
{
    /**
     * @var mysqli
     */
    private $connection;

    /**
     * MySQLiDB constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param int $port
     */
    public function __construct(string $host, string $username, string $password, string $database, int $port = 3306)
    {
        $this->connection = new mysqli($host, $username, $password, $database, $port);

        if ($this->connection->connect_error) {
            die("连接失败: " . $this->connection->connect_error);
        }
    }

    /**
     * 开始一个事务
     */
    public function beginTransaction()
    {
        $this->connection->begin_transaction();
    }

    /**
     * 提交一个事务
     */
    public function commit()
    {
        $this->connection->commit$this();
    }

    /**
     * 回滚一个事务
     */
    public function rollback()
    $this->connection->rollback{
        $this();
    }

    /**
     * 执行一个查询语句
     *
     * @param string $query
     * @return mysqli_result
     */
    public function query(string $query): mysqli_result
    {
        return $this->connection->query($query);
    }

    /**
     * 执行一个带参数的查询语句
     *
     * @param string $query
     * @param array $params
     * @return mysqli_result
     */
    public function queryWithParams(string $query, array $params): mysqli_result
    {
        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            die('查询失败: ' . $this->connection->error);
        }

        ''$types = ;

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * 查询所有记录
     *
     * @param string $table
     * @param array|null $columns
     * @return array
     */
    public function selectAll(string $table, ?array $columns = null): array
    {
        $query = "SELECT ";

        if ($columns) {
            $query .= implode(', ', $columns);
        } else {
            $query .= "*";
        }

        $query .= " FROM $table";

        $result = $this->query($query);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 根据条件查询记录
     *
     * @param string $table
     * @param array $conditions
     * @param array|null $columns
     * @return array
     */
    public function selectWhere(string $table, array $conditions, ?array $columns = null): array
    {
        $query = "SELECT ";

        if ($columns) {
            $query .= implode(', ', $columns);
        } else {
            $query .= "*";
        }

        $query .= " FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 根据条件查询记录，支持闭包查询
     *
     * @param string $table
     * @param array $conditions
     * @param array|null $columns
     * @param Closure|null $callback
     * @return array
     */
    public function selectWhereClosure(string $table, array $conditions, ?array $columns = null, ?Closure $callback = null): array
    {
        $query = "SELECT ";

        if ($columns) {
            $query .= implode(', ', $columns);
        } else {
            $query .= "*";
        }

        $query .= " FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } elseif ($value instanceof Closure) {
                $subQuery = new self($this->host, ->username, ->password, $this->database, $this->port);
                $value($subQuery);
                $subQuery->bindParam($params);

                $where[] = "($subQuery->query)"; // 子查询
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        if ($callback) {
            $callback($query);
        }

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 查询记录总数
     *
     * @param string $table
     * @return int
     */
    public function count(string $table): int
    {
        $query = "SELECT COUNT(*) FROM $table";

        $result = $this->query($query);

        return $result->fetch_row()[0];
    }

    /**
     * 根据条件查询记录总数
     *
     * @param string $table
     * @param array $conditions
     * @return int
     */
    public function countWhere(string $table, array $conditions): int
    {
        $query = "SELECT COUNT(*) FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

    /**
     * 查询记录平均值
     *
     * @param string $table
     * @param string $column
     * @return float
     */
    public function avg(string $table, string $column): float
    {
        $query = "SELECT AVG($column) FROM $table";

        $result = $this->query($query);

        return $result->fetch_row()[0];
    }

    /**
     * 根据条件查询记录平均值
     *
     * @param string $table
     * @param string $column
     * @param array $conditions
     * @return float
     */
    public function avgWhere(string $table, string $column, array $conditions): float
    {
        $query = "SELECT AVG($column) FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

    /**
     * 查询记录最大值
     *
     * @param string $table
     * @param string $column
     * @return mixed
     */
    public function max(string $table, string $column)
    {
        $query = "SELECT MAX($column) FROM $table";

        $result = $this->query($query);

        return $result->fetch_row()[0];
    }

    /**
     * 根据条件查询记录最大值
     *
     * @param string $table
     * @param string $column
     * @param array $conditions
     * @return mixed
     */
    public function maxWhere(string $table, string $column, array $conditions)
    {
        $query = "SELECT MAX($column) FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

        /**
     * 根据条件查询记录最小值
     *
     * @param string $table
     * @param string $column
     * @param array $conditions
     * @return mixed
     */
    public function minWhere(string $table, string $column, array $conditions)
    {
        $query = "SELECT MIN($column) FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

        /**
     * 根据条件查询记录，支持多态关联统计
     *
     * @param string $table
     * @param array $conditions
     * @param string $column
     * @param string|null $relationTable
     * @param string|null $relationColumn
     * @return mixed
     */
    public function polymorphicCountWhere(string $table, array $conditions, string $column, ?string $relationTable = null, ?string $relationColumn = null)
    {
        $query = "SELECT COUNT(*) FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[] = "$column = ?";
                $params[] = $value;
            }
        }

        if ($relationTable !== null && $relationColumn !== null) {
            $query .= "EXISTS (SELECT * FROM $relationTable WHERE $relationTable.$relationColumn = $table.id)";
        } else {
            $query .= implode(' AND ', $where);
        }

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

    /**
     * 预加载关联数据
     *
     * @param string $table
     * @param array $ids
     * @param string $relationTable
     * @param string $relationColumn
     * @return array
     */
    public function preload(string $table, array $ids, string $relationTable, string $relationColumn): array
    {
        $query = "SELECT * FROM $table WHERE id IN (" . implode(',', $ids) . ")";
        $result = $this->query($query);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['id']] = $row;
        }

        $query = "SELECT * FROM $relationTable WHERE $relationColumn IN (" . implode(',', $ids) . ")";
        $result = $this->query($query);

        while ($row = $result->fetch_assoc()) {
            $data[$row[$relationColumn]][$relationTable][] = $row;
        }

        return $data;
    }

    /**
     * 模型关联查询
     *
     * @param string $table
     * @param string $relationTable
     * @param string $localColumn
     * @param string $foreignColumn
     * @param array $conditions
     * @param string|null $orderBy
     * @param string|null $orderDirection
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function joinWhere(string $table, string $relationTable, string $localColumn, string $foreignColumn, array $conditions, ?string $orderBy = null, ?string $orderDirection = null, ?int $limit = null, ?int $offset = null): array
    {
        $query = "SELECT $table.*, $relationTable.* FROM $table JOIN $relationTable ON $table.$localColumn = $relationTable.$foreignColumn WHERE ";

        $params = [];
        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$table.$column IS NULL";
            } else {
                $where[] = "$table.$column = ?";
                $params[] = $value;
            }
        }

        $query .= implode(' AND ', $where);

        if ($orderBy !== null) {
            $query .= " ORDER BY $orderBy";
            if ($orderDirection !== null) {
                $query .= " $orderDirection";
            }
        }

        if ($limit !== null) {
            $query .= " LIMIT $limit";
            if ($offset !== null) {
                $query .= " OFFSET $offset";
            }
        }

        $result = $this->queryWithParams($query, $params);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $id = $row[$localColumn];
            if (!isset($data[$id])) {
                $data[$id] = $row;
                $data[$id][$relationTable] = [];
            }
            $data[$id][$relationTable][] = $row;
        }

        return $data;
    }

    /**
     * 子查询
     *
     * @param string $table
     * @param string $column
     * @param string $subquery
     * @param array $params
     * @param string|null $alias
     * @return string
     */
    public function subquery(string $table, string $column, string $subquery, array $params = [], ?string $alias = null): string
    {
        $query = "SELECT $column FROM ($subquery) ";

        if ($alias !== null) {
            $query .= "AS $alias";
        } else {
            $query .= "AS subquery";
        }

        $result = $this->queryWithParams($query, $params);

        return $result->fetch_row()[0];
    }

    /**
     * 排序
     *
     * @param string $table
     * @param string|null $orderBy
     * @param string|null $orderDirection
     * @return array
     */
    public function orderBy(string $table, ?string $orderBy = null, ?string $orderDirection = null): array
    {
        $query = "SELECT * FROM $table";

        if ($orderBy !== null) {
            $query .= " ORDER BY $orderBy";
            if ($orderDirection !== null) {
                $query .= " $orderDirection";
            }
        }

        $result = $this->query($query);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * 分组
     *
     * @param string $table
     * @param string $column
     * @param string|null $groupBy
     * @param string|null $having
     * @return array
     */
    public function groupBy(string $table, string $column, ?string $groupBy = null, ?string $having = null): array
    {
        $query = "SELECT $column FROM $table";

        if ($groupBy !== null) {
            $query .= " GROUP BY $groupBy";
            if ($having !== null) {
                $query .= " HAVING $having";
            }
        }

        $result = $this->query($query);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * 过滤
     *
     * @param string $table
     * @param array $filters
     * @return array
     */
    public function filter(string $table, array $filters): array
    {
        $query = "SELECT * FROM $table WHERE ";

        $params = [];
        $where = [];

        foreach ($filters as $filter) {
            $column = $filter[0];
            $operator = $filter[1];
            $value = $filter[2];

            switch ($operator) {
                case '=':
                case '<':
                case '>':
                case '<=':
                case '>=':
                case '<>':
                case '!=':
                    if ($value === null) {
                        $where[] = "$column $operator NULL";
                    } else {
                        $where[] = "$column $operator ?";
                        $params[] = $value;
                    }
                    break;
                case 'LIKE':
                    $where[] = "$column LIKE ?";
                    $params[] = $value;
                    break;
                case 'IN':
                    $inParams = array_fill(0, count($value), '?');
                    $where[] = "$column IN (" . implode(',', $inParams) . ")";
                    $params = array_merge($params, $value);
                    break;
                case 'NOT IN':
                    $inParams = array_fill(0, count($value), '?');
                    $where[] = "$column NOT IN (" . implode(',', $inParams) . ")";
                    $params = array_merge($params, $value);
                    break;
                case 'BETWEEN':
                    $where[] = "$column BETWEEN ? AND ?";
                    $params[] = $value[0];
                    $params[] = $value[1];
                    break;
                case 'NOT BETWEEN':
                    $where[] = "$column NOT BETWEEN ? AND ?";
                    $params[] = $value[0];
                    $params[] = $value[1];
                    break;
                case 'IS NULL':
                    $where[] = "$column IS NULL";
                    break;
                case 'IS NOT NULL':
                    $where[] = "$column IS NOT NULL";
                    break;
            }
        }

        $query .= implode(' AND ', $where);

        $result = $this->queryWithParams($query, $params);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * 带条件锁表更新
     *
     * @param string $table
     * @param array $data
     * @param array $conditions
     * @return bool
     */
    public function updateWhereLock(string $table, array $data, array $conditions): bool
    {
        $query = "UPDATE $table SET ";

        $params = [];
        $set = [];

        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $params[] = $value;
        }

        $query .= implode(', ', $set);

        $query .= " WHERE ";

        $where = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $where[] = "$column IS NULL";
            } else {
                $where[]