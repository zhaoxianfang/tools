<?php

namespace zxf\mysql\Driver;

use Closure;
use Exception;
use zxf\mysql\Contracts\MysqlInterface;

/**
 * mysqli 基础操作类
 * 需要php 开启mysqli 扩展
 */
class MysqliDriver implements MysqlInterface
{
    // mysqli 连接信息
    private $conn;
    // 查询的SQL
    protected string $query = '';

    /**
     * 被查询字段列表,
     * eg:[id,name]、[SUM(number)]
     *
     * @var array|string[]
     */
    protected array $fieldAssemble = ['*'];

    /**
     * 关联查询数组列表
     * eg:[table_name,on_where,left|right|inner]、[sql_str]
     *
     * @var array
     */
    protected array $joinAssemble = [];
    /**
     * where 查询条件
     * eg:[$column, $operator = null, $value = null, $type = 'and|or']
     *
     * @var array
     */
    protected array $whereAssemble = [];
    /**
     * 分组
     * eg:[$field]、[$field,$condition,$operator=having]
     *
     * @var array
     */
    protected array $groupAssemble = [];
    /**
     * 排序
     * eg:[$field,$handle='ASC|DESC'],
     *
     * @var array
     */
    protected array $orderAssemble = [];

    /**
     * 批量更新修改或插入的数据
     * eg:['name'=>'','gender'=>'']
     *
     * @var array
     */
    protected array $fillAssemble = [];
    /**
     * eg: 0,10
     *
     * @var string
     */
    protected string $limit = '';
    // sql string
    protected string $subQuery = '';
    // 表名
    protected string $tableName = '';
    // 错误信息
    protected string $error = '';

    // 构造函数
    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = 3306, $charset = 'utf8mb4', $socket = null)
    {
        if (!extension_loaded('mysqli')) {
            $this->error = '不支持的扩展:mysqli';
            throw new Exception($this->error);
        }
        $params = compact('hostname', 'username', 'password', 'database', 'port', 'socket');

        $mysqlIc    = new \ReflectionClass('mysqli');
        $this->conn = $mysqlIc->newInstanceArgs($params);
        if ($this->conn->connect_error) {
            $this->error = "连接失败: " . $this->conn->connect_error;
            throw new Exception($this->error);
        }
        if ($charset) {
            $this->setCharset($charset);
        }
    }

    // 设置字符集
    public function setCharset(string $charset = 'utf8mb4')
    {
        $this->conn->set_charset($charset);
        return $this;
    }

    // 设置表名
    public function table(string $tableName = '')
    {
        $this->tableName = $tableName;
        return $this;
    }

    private function analyseFields()
    {
        if (!empty($this->fieldAssemble)) {
            $this->query .= ' ' . implode(', ', array_map(function ($value) {
                    return $this->conn->real_escape_string($value);
                }, $this->fieldAssemble));
        } else {
            $this->query .= ' *';
        }
        return $this;

    }

    private function analyseJoin()
    {
//        eg:[table_name,on_where,left|right|inner]、[sql_str]
        if (!empty($this->joinAssemble)) {
            $this->query .= ' ' . implode(', ', array_map(function ($value) {
                    return $this->conn->real_escape_string($value);
                }, $this->joinAssemble));
        }
        return $this;
    }

    private function analyseWhere()
    {

    }

    private function analyseGroup()
    {

    }

    private function analyseOrder()
    {

    }

    private function analyseLimit()
    {

    }

    private function analyseFill()
    {
        $data = $this->fillAssemble;

        $columns = implode(', ', array_keys($data));
        $values  = implode(', ', array_map(function ($value) {
            return "'" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data)));

        $this->query .= ' (' . $columns . ') VALUES (' . $values . ')';
        return $this;
    }

    private function analyseQuery()
    {
        $this->analyseFields();
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        return $this;
    }

    public function insert(array $data)
    {
        $this->fillAssemble = $data;

        $this->query = 'INSERT INTO ' . $this->tableName;
        $this->analyseQuery();

        $result = $this->query($this->query);
        if (!$result) {
            throw new \Exception($this->conn->error);
        }
        return $this->conn->insert_id;
    }

    public function update($table, $data, $where = "")
    {
        $sets = [];

        foreach ($data as $key => $value) {
            $sets[] = "$key='" . $this->conn->real_escape_string($value) . "'";
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

    public function delete($table, $where = "")
    {
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

    /**
     * 聚合查询
     *
     * @param string $table    表名
     * @param string $field    聚合字段名
     * @param string $function 聚合函数名称，例如 COUNT,AVG,SUM,MAX 等
     * @param string $where
     *
     * @return false|mixed
     */
    public function aggregate($table, $field, $function, $where = "")
    {
        $sql = "SELECT $function($field) AS $function FROM $table";
        if ($where != "") {
            $sql .= " WHERE $where";
        }
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row[$function];
        } else {
            return false;
        }
    }

    public function count($table, $where = "")
    {
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

    public function sum($table, $column, $where = "")
    {
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

    public function avg($table, $column, $where = "")
    {
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

    public function max($table, $column, $where = "")
    {
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

    public function min($table, $column, $where = "")
    {
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

    // 批量插入数据
    public function insertBatch($tableName, $data)
    {
        $fields = implode(",", array_keys($data[0]));
        $values = "";
        foreach ($data as $item) {
            $values .= "('" . implode("','", array_values($item)) . "'),";
        }
        $values = rtrim($values, ",");
        $sql    = "INSERT INTO $tableName ($fields) VALUES $values";
        if ($this->conn->query($sql) === true) {
            return true;
        } else {
            echo "数据插入失败: " . $this->conn->error;
        }
    }

    // 批量更新数据
    public function updateBatch($tableName, $data, $primaryKey)
    {
        $sql = "UPDATE $tableName SET ";
        foreach ($data as $item) {
            $values = "";
            foreach ($item as $key => $value) {
                if ($key != $primaryKey) {
                    $values .= "$key='$value',";
                }
            }
            $values = rtrim($values, ",");
            $sql    .= "$values WHERE $primaryKey=" . $item[$primaryKey] . ";";
        }
        if ($this->conn->multi_query($sql) === true) {
            return true;
        } else {
            echo "数据更新失败: " . $this->conn->error;
        }
    }

    public function hasMany($table, $foreign_key, $where = "")
    {
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

    public function belongsTo($table, $foreign_key, $where = "")
    {
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

    public function hasOne($table, $foreign_key, $where = "")
    {
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

    public function hasManyThrough($table, $through_table, $foreign_key, $through_foreign_key, $where = "")
    {
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

    // 预加载
    public function preload($table, $foreign_key, $where = "")
    {
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

    // 子查询
    public function subQuery($table, $columns = "*", $where = "", $limit = "", $offset = "", $orderBy = "", $groupBy = "")
    {
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

    // 锁表更新
    public function lockForUpdate($table, $data, $where = "")
    {
        $this->conn->autocommit(false);

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

    // 事务操作
    public function transaction($callback): bool
    {
        try {
            $this->conn->autocommit(false);
            $callback($this);
            $this->conn->commit();
            $this->conn->autocommit(true);
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->autocommit(true);
            return false;
        }
    }

    // 批量更新
    public function batchUpdate($tableName, $data, $where)
    {
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
        $sql = "UPDATE $tableName SET $set WHERE $where";
        return $this->conn->query($sql);
    }

    /**
     * 当 $column 不为空时候执行闭包查询
     *
     * @param         $column
     * @param Closure $callback
     *
     * @return $this
     */
    public function when($column, Closure $callback)
    {
        if ($column) {
            $callback($this);
        }
        return $this;
    }

    /**
     * 当 $column 为空时候执行闭包查询
     *
     * @param         $column
     * @param Closure $callback
     *
     * @return $this
     */
    public function whenNull($column, Closure $callback)
    {
        if (empty($column)) {
            $callback($this);
        }
        return $this;
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure) {
            return $column($this);
        }
        $this->where[] = [$column, $operator, $value, $boolean];
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        // TODO: Implement orWhere() method.
    }

    public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
    {
        // TODO: Implement whereNot() method.
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->whereAssemble[] = [$column, $values, $boolean, $not];
        return $this;
    }

    public function limit($offset = 0, $limit = 10)
    {
        $this->limit = " $offset,$limit";
        return $this;
    }

    public function groupBy($groupByField)
    {
        $this->groupAssemble[] = $groupByField;
        return $this;
    }

    public function first($columns = ['*'])
    {
        $this->fieldAssemble = $columns;
    }

    public function fill(array $attributes)
    {
        $this->dataAssemble = $attributes;
        return $this;
    }

    public function save(array $options = [])
    {
        if (isset($this->fields['id'])) {
            $id = $this->fields['id'];
            unset($this->fields['id']);
            $result             = $this->conn->update($this->table, $this->fields, "id=$id");
            $this->fields['id'] = $id;
        } else {
            $id                 = $this->conn->insert($this->table, $this->fields);
            $this->fields['id'] = $id;
        }
        return $id;
    }


    public function get($columns = ['*'])
    {
        $this->fieldAssemble = $columns;

        $fields = implode(", ", array_keys($this->fields));
        $where  = $this->where;
        $order  = $this->order;
        $limit  = $this->limit;
        $joins  = $this->joins;
        $sql    = "SELECT $fields FROM $this->table";
        if ($where != "") {
            $sql .= " WHERE $where";
        }
        if ($order != "") {
            $sql .= " ORDER BY $order";
        }
        if ($limit != "") {
            $sql .= " LIMIT $limit";
        }
        foreach ($joins as $join) {
            $table  = $join[0];
            $field1 = $join[1];
            $field2 = $join[2];
            $type   = $join[3];
            switch ($type) {
                case "hasOne":
                    $sql .= " LEFT JOIN $table ON $this->table.$field1=$table.$field2";
                    break;
                case "hasMany":
                    $sql .= " LEFT JOIN $table ON $this->table.$field1=$table.$field2";
                    break;
                case "belongsTo":
                    $sql .= " LEFT JOIN $table ON $this->table.$field1=$table.$field2";
                    break;
                case "belongsToMany":
                    $pivot = $this->table . "_" . $table;
                    $sql   .= " LEFT JOIN $pivot ON $this->table.id=$pivot.$field1";
                    $sql   .= " LEFT JOIN $table ON $pivot.$field2=$table.id";
                    break;
            }
        }
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $model = new Model($this->connection, $this->table);
                foreach ($row as $key => $value) {
                    $model->$key = $value;
                }
                $rows[] = $model;
            }
            return $rows;
        } else {
            return false;
        }
    }

    //  未继承接口部分  ===============================

    // 执行 SQL 查询
    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    // 获取上一次插入操作的 ID
    public function insert_id()
    {
        return $this->conn->insert_id;
    }

    // 获取错误信息
    public function error()
    {
        return !empty($this->conn->error) ? $this->conn->error : $this->error;
    }

    // 获取错误编号
    public function errno()
    {
        return $this->conn->errno;
    }

    // 关闭连接
    public function close()
    {
        $this->conn->close();
    }

    // 转义字符串
    public function escape_string($str)
    {
        return $this->conn->real_escape_string($str);
    }

    // 开始事务
    public function begin_transaction()
    {
        return $this->conn->begin_transaction();
    }

    // 提交事务
    public function commit()
    {
        return $this->conn->commit();
    }

    // 回滚事务
    public function rollback()
    {
        return $this->conn->rollback();
    }

    // 获取客户端信息
    public function client_info()
    {
        return $this->conn->client_info;
    }

    // 获取客户端版本
    public function client_version()
    {
        return $this->conn->client_version;
    }

    // 获取协议版本
    public function protocol_version()
    {
        return $this->conn->protocol_version;
    }

    // 获取服务器信息
    public function server_info()
    {
        return $this->conn->server_info;
    }

    // 获取服务器版本
    public function server_version()
    {
        return $this->conn->server_version;
    }

    // 获取主机信息
    public function host_info()
    {
        return $this->conn->host_info;
    }

    // 获取数据库名称
    public function db_name()
    {
        return $this->conn->db;
    }

    // 获取字符集
    public function charset()
    {
        return $this->conn->charset;
    }

    // 获取客户端字符集
    public function client_charset()
    {
        return $this->conn->client_charset;
    }

    // 获取默认字符集
    public function character_set_name()
    {
        return $this->conn->character_set_name();
    }

    // 获取自动提交状态
    public function autocommit()
    {
        return $this->conn->autocommit;
    }

    // 设置自动提交状态
    public function set_autocommit($mode)
    {
        return $this->conn->autocommit($mode);
    }

    // 获取当前事务状态
    public function transaction_status()
    {
        return $this->conn->transaction_status();
    }

    // 获取最后一次 SQL 查询的错误信息
    public function sqlstate()
    {
        return $this->conn->sqlstate;
    }

    // 获取最后一次 SQL 查询的错误编号
    public function sql_errno()
    {
        return $this->conn->errno;
    }

    // 获取最后一次 SQL 查询的错误信息
    public function sql_error()
    {
        return $this->conn->error;
    }

    // 获取最后一次 SQL 查询的语句
    public function last_query()
    {
        return $this->conn->last_query;
    }

    // 获取最后一次 SQL 查询的结果集
    public function last_result()
    {
        return $this->conn->last_result;
    }

    // 获取最后一次 SQL 查询的行数
    public function affected_rows()
    {
        return $this->conn->affected_rows;
    }

    // 获取最后一次 SQL 查询的字段数
    public function field_count()
    {
        return $this->conn->field_count;
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function fetch_fields()
    {
        return $this->conn->fetch_fields();
    }

    // 获取最后一次 SQL 查询的结果集中的行信息
    public function fetch_row()
    {
        return $this->conn->fetch_row();
    }

    // 获取最后一次 SQL 查询的结果集中的所有行信息
    public function fetch_all()
    {
        return $this->conn->fetch_all();
    }

    // 获取最后一次 SQL 查询的结果集中的一行信息
    public function fetch_assoc()
    {
        return $this->conn->fetch_assoc();
    }

    // 获取最后一次 SQL 查询的结果集中的一行信息
    public function fetch_array()
    {
        return $this->conn->fetch_array();
    }

    // 获取最后一次 SQL 查询的结果集中的一行信息
    public function fetch_object()
    {
        return $this->conn->fetch_object();
    }

    // 释放最后一次 SQL 查询的结果集
    public function free_result()
    {
        return $this->conn->free_result();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_seek($fieldnr)
    {
        return $this->conn->field_seek($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_tell()
    {
        return $this->conn->field_tell();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function fetch_field()
    {
        return $this->conn->fetch_field();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function fetch_field_direct($fieldnr)
    {
        return $this->conn->fetch_field_direct($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_name($fieldnr)
    {
        return $this->conn->field_name($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_table($fieldnr)
    {
        return $this->conn->field_table($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_len($fieldnr)
    {
        return $this->conn->field_len($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_type($fieldnr)
    {
        return $this->conn->field_type($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function field_flags($fieldnr)
    {
        return $this->conn->field_flags($fieldnr);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function num_fields()
    {
        return $this->conn->num_fields();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function num_rows()
    {
        return $this->conn->num_rows();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function ping()
    {
        return $this->conn->ping();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function more_results()
    {
        return $this->conn->more_results();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function next_result()
    {
        return $this->conn->next_result();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function prepare($query)
    {
        return $this->conn->prepare($query);
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function stmt_init()
    {
        return $this->conn->stmt_init();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function store_result()
    {
        return $this->conn->store_result();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function use_result()
    {
        return $this->conn->use_result();
    }

    // 获取最后一次 SQL 查询的结果集中的字段信息
    public function get_warnings()
    {
        return $this->conn->get_warnings();
    }

    //   封装部份==========================================

    // 查询数据
    public function select($table, $columns = "*", $join = [], $where = null, $group_by = null, $order_by = null, $limit = null, $offset = null): array
    {
        $sql = "SELECT $columns FROM $table";
        if ($join) {
            $sql .= " " . $join['type'] . " JOIN " . $join['table'] . " ON " . $join['table'];
        }
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
        $result = $this->conn->query($sql);
        $data   = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

}