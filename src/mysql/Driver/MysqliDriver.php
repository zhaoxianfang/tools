<?php

namespace zxf\mysql\Driver;

use Closure;
use Exception;
use ReflectionClass;
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
    protected $query = '';

    /**
     * 被查询字段列表,
     * eg:[id,name]、[SUM(number)]
     *
     * @var array|string
     */
    protected $fieldAssemble = ['*'];

    /**
     * 关联查询数组列表
     * eg:[table_name,on_where,left|right|inner]、[sql_str]
     *
     * @var array
     */
    protected $joinAssemble = [];
    /**
     * where 查询条件
     * eg:[$column, $operator = null, $value = null, $type = 'and|or']
     *
     * @var array
     */
    protected $whereAssemble = [];
    /**
     * 分组
     * eg:[$field]、[$field,$condition,$operator=having]
     *
     * @var array
     */
    protected $groupAssemble = [];
    /**
     * 排序
     * eg:[$field,$handle='ASC|DESC'],
     *
     * @var array
     */
    protected $orderAssemble = [];

    /**
     * 批量更新修改或插入的数据
     * eg:['name'=>'','gender'=>'']
     *
     * @var array
     */
    protected $fillAssemble = [];
    /**
     * eg: 0,10
     *
     * @var string
     */
    protected $limit = '';
    // sql string
    protected $subQuery = '';
    // 表名
    protected $tableName = '';
    // 错误信息
    protected $error = '';

    // 构造函数
    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = 3306, $charset = 'utf8mb4', $socket = null)
    {
        if (!extension_loaded('mysqli')) {
            $this->error = '不支持的扩展:mysqli';
            throw new Exception($this->error);
        }
        $params = compact('hostname', 'username', 'password', 'database', 'port', 'socket');

        $mysqlIc    = new ReflectionClass('mysqli');
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
    public function setCharset(string $charset = 'utf8mb4'): MysqliDriver
    {
        $this->conn->set_charset($charset);
        return $this;
    }

    public function reset()
    {
        $this->query         = '';
        $this->fieldAssemble = ['*'];
        $this->joinAssemble  = [];
        $this->whereAssemble = [];
        $this->groupAssemble = [];
        $this->orderAssemble = [];
        $this->fillAssemble  = [];
        $this->limit         = '';
        $this->subQuery      = '';
        $this->error         = '';
        return $this;
    }

    // 设置表名
    public function table(string $tableName = ''): MysqliDriver
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * 解析 查询字段
     *
     * @param array $fields 不为空表示仅解析，为空表示需要解析到 query 字符串中中
     *
     * @return string|MysqliDriver
     */
    private function analyseFields(array $fields = [])
    {
        $str         = '';
        $onlyAnalyse = !empty($fields);
        $fieldsList  = !empty($fields) ? $fields : $this->fieldAssemble;

        if (!empty($fieldsList)) {
            $str .= ' ' . implode(', ', array_map(function ($value) {
                    return $this->conn->real_escape_string($value);
                }, $fieldsList)) . ' ';
        }
        if (!$onlyAnalyse) {
            $this->query .= $str;
        }
        return $onlyAnalyse ? $str : $this;
    }

    /**
     * 解析关联查询
     * eg:[table_name,on_where,left|right|inner]、[sql_str]
     *
     * @param array $joins 不为空表示仅解析，为空表示需要解析到 query 字符串中中
     *
     * @return string|MysqliDriver
     */
    private function analyseJoin(array $joins = [])
    {
        $str         = '';
        $onlyAnalyse = !empty($joins);
        $joinsList   = !empty($joins) ? $joins : $this->joinAssemble;
        if (!empty($joinsList)) {
            $str .= ' ' . implode(' ', array_map(function ($join) {
                    if (count($join) < 2) {
                        return $join[0];
                    }
                    return strtoupper(!empty($join[2]) ? $join[2] : 'INNER') . ' JOIN ' . $join[0] . ' ON ' . $this->analyseWhere($join[1]);
                }, $joinsList));
        }
        if (!$onlyAnalyse) {
            $this->query .= $str;
        }
        return $onlyAnalyse ? $str : $this;
    }


    /**
     * 解析 where 查询
     * eg:[$column, $operator = null, $value = null, $type = 'and|or']
     *
     * @param array|string $where 不为空表示仅解析，为空表示需要解析到 query 字符串中
     *
     * @return string|MysqliDriver
     */
    private function analyseWhere($where = '')
    {
        $str         = '';
        $onlyAnalyse = !empty($where);
        $whereList   = !empty($where) ? $where : $this->whereAssemble;
        if (is_string($where)) {
            return $where;
        }
        if (!empty($whereList)) {
            foreach ($whereList as $item) {
                if ($item instanceof Closure) {
                    if (!empty($str)) {
                        $str .= ' AND ';
                    }
                    $str .= $item($this);
                } else {
                    if (!empty($str)) {
                        $str .= ' ' . (!empty($item[3]) ? strtoupper($item[3]) : 'AND') . ' ';
                    }
                    $str .= $item[0] . " " . $item[1] . " " . $item[2];
                }
            }
        }
        if (!empty($str)) {
            $str = ' WHERE ' . $str;
        }
        if (!$onlyAnalyse) {
            $this->query .= $str;
        }
        return $onlyAnalyse ? $str : $this;
    }

    /**
     * 分组查询
     * eg:[$field]、[$field,$condition,$operator=having]
     *
     * @param array $groups 不为空表示仅解析，为空表示需要解析到 query 字符串中
     *
     * @return string|MysqliDriver
     */
    private function analyseGroup(array $groups = [])
    {
        $str         = '';
        $onlyAnalyse = !empty($groups);
        $groupList   = !empty($groups) ? $groups : $this->groupAssemble;
        if (!empty($groupList)) {
            $str .= implode(' ', array_map(function ($group) {
                if (count($group) < 2) {
                    return $group[0];
                }
                return $group[0] . ' ' . $group[2] . ' ' . $this->conn->real_escape_string($group[1]);
            }, $groupList));
        }
        if (!empty($str)) {
            $str = ' GROUP BY ' . $str;
        }
        if (!$onlyAnalyse) {
            $this->query .= $str;
        }
        return $onlyAnalyse ? $str : $this;
    }

    /**
     * 字段排序
     * eg:[$field,$handle='ASC|DESC'],
     *
     * @param string|array $orderBy 不为空表示仅解析，为空表示需要解析到 query 字符串中
     *
     * @return string|MysqliDriver
     */
    private function analyseOrder($orderBy = '')
    {
        $str         = '';
        $onlyAnalyse = !empty($orderBy);
        $orderList   = !empty($orderBy) ? $orderBy : $this->orderAssemble;
        if (is_string($orderList)) {
            return $orderList;
        }
        if (!empty($orderList)) {
            $str .= implode(' ', array_map(function ($order) {
                if (count($order) < 2) {
                    return $order[0] . ' DESC';
                }
                return $order[0] . ' ' . $order[1];
            }, $orderList));
        }
        if (!empty($str)) {
            $str = ' ORDER BY ' . $str;
        }
        if (!$onlyAnalyse) {
            $this->query .= $str;
        }
        return $onlyAnalyse ? $str : $this;
    }

    private function analyseLimit($limit = '')
    {
        $onlyAnalyse = !empty($limit);
        $str         = empty($limit) ? $this->limit : $limit;
        if (!empty($str) && is_array($str)) {
            $str = $str[0] . ',' . $str[1];
        }
        if (empty($str)) {
            $str = 1;
        }
        if ($onlyAnalyse) {
            return ' LIMIT ' . $str;
        }
        $this->query .= ' LIMIT ' . $str;

        return $this;
    }

    private function analyseFill()
    {
        // TODO
        $data = $this->fillAssemble;

        $columns = implode(', ', array_keys($data));
        $values  = implode(', ', array_map(function ($value) {
            return "'" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data)));

        $this->query .= ' (' . $columns . ') VALUES (' . $values . ')';
        return $this;
    }

    private function packageQuery()
    {
        $this->query = 'SELECT ';
        $this->analyseFields();
        $this->query .= ' FROM ' . $this->tableName;
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

        $columns = implode(', ', array_keys($data));
        $values  = implode(', ', array_map(function ($value) {
            return "'" . $this->conn->real_escape_string($value) . "'";
        }, array_values($data)));


        $this->query = 'INSERT INTO ' . $this->tableName . ' (' . $columns . ') VALUES (' . $values . ')';
        $this->analyseWhere();
        $result = $this->query($this->query);
        if (!$result) {
            throw new Exception($this->conn->error);
        }
        return $this->conn->insert_id;
    }

    public function update($data)
    {
        $sets = [];

        foreach ($data as $key => $value) {
            $sets[] = "$key='" . $this->conn->real_escape_string($value) . "'";
        }
        $this->query = "UPDATE $this->tableName SET " . implode(", ", $sets);
        $this->analyseWhere();

        $result = $this->conn->query($this->query);

        if (!$result) {
            throw new Exception('更新失败:' . $this->conn->error);
        }

        return $this->conn->affected_rows;
    }

    public function delete()
    {
        $this->query = "DELETE FROM $this->tableName";
        $this->analyseWhere();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('删除失败:' . $this->conn->error);
        }
        return $this->conn->affected_rows;
    }

    public function count(string $field = '*')
    {
        $this->fieldAssemble = '';
        $this->query         = "SELECT COUNT(" . $field . ") AS count" . ($field == '*' ? '' : '_' . $field) . " FROM $this->tableName";
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $result->fetch_assoc()['count'];
    }

    public function sum(string $field = '*')
    {
        $this->fieldAssemble = '';
        $this->query         = "SELECT SUM(" . $field . ") AS sum" . ($field == '*' ? '' : '_' . $field) . " FROM $this->tableName";
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $result->fetch_assoc()['sum'];
    }

    public function avg(string $field = '*')
    {
        $this->fieldAssemble = '';
        $this->query         = "SELECT AVG(" . $field . ") AS avg" . ($field == '*' ? '' : '_' . $field) . " FROM $this->tableName";
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $result->fetch_assoc()['avg'];
    }

    public function max(string $field = '*')
    {
        $this->fieldAssemble = '';
        $this->query         = "SELECT MAX(" . $field . ") AS max" . ($field == '*' ? '' : '_' . $field) . " FROM $this->tableName";
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $result->fetch_assoc()['max'];
    }

    public function min(string $field = '*')
    {
        $this->fieldAssemble = '';
        $this->query         = "SELECT MIN(" . $field . ") AS min" . ($field == '*' ? '' : '_' . $field) . " FROM $this->tableName";
        $this->analyseJoin();
        $this->analyseWhere();
        $this->analyseGroup();
        $this->analyseOrder();
        $this->analyseLimit();
        $result = $this->conn->query($this->query);
        if (!$result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $result->fetch_assoc()['min'];
    }

    // 批量插入数据
    public function insertBatch($data)
    {
        $fields = implode(",", array_keys($data[0]));
        $values = "";
        foreach ($data as $item) {
            $values .= "('" . implode("','", array_values($item)) . "'),";
        }
        $values = rtrim($values, ",");
        $sql    = "INSERT INTO $this->tableName ($fields) VALUES $values";
        if ($this->conn->query($sql) === true) {
            return true;
        } else {
            throw new Exception('数据插入失败:' . $this->conn->error);
        }
    }

    // 批量更新数据
    public function updateBatch($data)
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key='" . $this->conn->real_escape_string($value) . "'";
        }
        $this->query = "UPDATE $this->tableName SET " . implode(", ", $sets);
        $this->analyseWhere();
        if ($this->conn->multi_query($this->query) === true) {
            return true;
        } else {
            throw new Exception('数据更新失败:' . $this->conn->error);
        }
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
            throw new Exception('更新失败:' . $this->conn->error);
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

    //eg:[$column, $operator = null, $value = null, $type = 'and|or']
    public function where($column, $operator = null, $value = null, $type = 'and')
    {
        if ($column instanceof Closure) {
            return $column($this);
        }
        $this->whereAssemble[] = [$column, $operator, $value, $type];
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if ($column instanceof Closure) {
            return $column($this);
        }
        $this->whereAssemble[] = [$column, $operator, $value, 'or'];
        return $this;
    }

    public function whereNot($column, $value = null)
    {
        $this->whereAssemble[] = [$column, '<>', $value, 'and'];
        return $this;
    }

    public function whereIn(string $column, $value = null)
    {
        $this->whereAssemble[] = [$column, 'IN', $value, 'and'];
        return $this;
    }

    public function limit(int $offset = 0, $limit = 10)
    {
        $this->limit = " $offset,$limit";
        return $this;
    }

    public function groupBy($groupByField)
    {
        $this->groupAssemble[] = is_array($groupByField) ? $groupByField : [$groupByField];
        return $this;
    }

    public function fill(array $attributes)
    {
        $this->fillAssemble = $attributes;
        return $this;
    }

    public function first($columns = ['*'])
    {
        $this->fieldAssemble = (is_array($columns) && count($columns) < 2 && in_array('*', $columns)) ? $this->fieldAssemble : $columns;
        $this->limit         = 1;
        $this->packageQuery();
        $this->result = $this->conn->query($this->query);
        if (!$this->result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $this;
    }

    public function save(array $data = [])
    {
        $this->fillAssemble = empty($data) ? $this->fillAssemble : $data;
        if (isset($this->fillAssemble['id'])) {
            $id = $this->fillAssemble['id'];
            unset($this->fillAssemble['id']);
            $this->where('id', '=', $id)->update($data);
            $this->fillAssemble['id'] = $id;
        } else {
            $id                       = $this->insert($data);
            $this->fillAssemble['id'] = $id;
        }
        return $id;
    }


    public function get($columns = ['*'])
    {
        $this->fillAssemble = empty($columns) ? $this->fillAssemble : $columns;

        $this->packageQuery();

        $this->result = $this->conn->query($this->query);
        if (!$this->result) {
            throw new Exception('查询失败:' . $this->conn->error);
        }
        return $this;
    }

    public function toSQL(): string
    {
        $this->packageQuery();
        return $this->conn->prepare($this->query);
    }

    // 查询的数据字段
    public function field($columns = ["*"])
    {
        $this->fieldAssemble = $columns;
        return $this;
    }

    // 执行 SQL 查询
    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    // 结果转为数组
    public function toArray()
    {
        $rows = [];
        while ($row = $this->result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    // 结果转为数组
    public function toJson():string
    {
        $rows = [];
        while ($row = $this->result->fetch_assoc()) {
            $rows[] = $row;
        }
        return json_encode($rows);
    }

    //  未继承接口部分  ===============================


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
}