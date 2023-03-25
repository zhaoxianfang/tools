<?php

namespace zxf\mysql;

use Exception;

/**
 * mysqli 基础操作类
 * 需要php 开启mysqli 扩展
 */
class MysqliBase
{
    private $conn;

    // 构造函数
    public function __construct($servername, $username, $password, $dbname)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            throw new Exception("连接失败: " . $this->conn->connect_error);
        }
    }

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
        return $this->conn->error;
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

    // 设置字符集
    public function set_charset($charset)
    {
        return $this->conn->set_charset($charset);
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
            echo "数据插入成功";
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

    // 子查询
    public function subQuery($tableName, $fields, $subQuery, $joinField = 'id')
    {
        $sql    = "SELECT $fields FROM $tableName WHERE $joinField IN ($subQuery)";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
        } else {
            return null;
        }
    }

    // 事务操作
    public function transaction($callback): bool
    {
        $this->conn->autocommit(false);
        try {
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
    public function batchUpdate($table, $data, $where)
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
        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->conn->query($sql);
    }

}