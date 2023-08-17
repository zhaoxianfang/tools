<?php

namespace zxf\Database\Driver\Mysql;

use Closure;
use \Exception;
use ReflectionClass;
use ReflectionException;
use zxf\Database\Driver\MySQLAbstract;

class MysqliDriver extends MySQLAbstract
{

    /**
     * @param string $connectionName
     * @param mixed  ...$args
     *
     * @throws ReflectionException
     */
    public function __construct($connectionName = 'default', ...$args)
    {
        if (!extension_loaded('mysqli')) {
            throw new Exception('不支持的扩展:mysqli');
        }
        $this->connect('default', ...$args);
    }

    // 设置字符集
    public function setCharset(string $charset = 'utf8mb4')
    {
        // 设置字符集为utf8mb4
        $this->conn->prepare("SET NAMES '{$charset}'");
        // $this->conn->set_charset($charset);
        return $this;
    }

    /**
     * @param string $connectionName
     * @param        ...$args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function connect($connectionName = 'default', ...$args)
    {
        if (empty($params = $this->getConfig($connectionName, ...$args))) {
            return false;
        }

        if (!extension_loaded('mysqli')) {
            throw new Exception('不支持的扩展:mysqli');
        }
        try {
            $mysqlIc    = new ReflectionClass('mysqli');
            $this->conn = $mysqlIc->newInstanceArgs($params);
            if ($this->conn->connect_error) {
                $this->error = "连接失败: " . $this->conn->connect_error;
                throw new Exception($this->error);
            }
            // 设置字符集
            $this->setCharset();
            return $this;
        } catch (Exception $e) {
            throw new Exception("Database 连接失败：" . $e->getMessage());
        }

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

    private function getConfig($connectionName = 'default', ...$args)
    {
        if (empty($args) || !is_array($config = $args[0]) || count($config) < 4 || empty($config['host']) || empty($config['dbname']) || empty($config['username']) || !isset($config['password'])) {
            if (!function_exists('config') || empty($config = config('tools_database.mysql.' . $connectionName))) {
                return false;
            }
        }

        $this->config = [
            'hostname' => $config['host'],
            'username' => $config['username'] ?? 'root',
            'password' => $config['password'] ?? '',
            'database' => $config['dbname'] ?? '',
            'port'     => $config['port'] ?? 3306,
            'socket'   => $config['socket'] ?? null,
            // 'charset'  => $config['charset'] ?? 'utf8mb4', // mysqli 中单独设置charset
        ];
        return $this->config;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function execute()
    {
        // 准备预处理语句
        empty($this->query) && $this->toSql();
        $stmt = $this->conn->prepare($this->query);
        if ($stmt) {
            $this->stmt = $stmt;
            // 绑定参数并执行查询
            $this->bindParam();
            $stmt->execute();
            $this->reset();
            return $stmt;
        } else {
            $this->reset();
            throw new Exception("数据库错误：" . $this->conn->error);
        }
    }

    private function bindParam()
    {
        //"i": 表示整数类型
        //"d": 表示双精度浮点数类型
        //"s": 表示字符串类型
        //"b": 表示二进制数据类型（例如 BLOB）

        $bindStr = '';
        foreach ($this->parameters as $value) {
            $bindStr .= is_numeric($value) ? 'd' : 's';
        }
        $this->stmt->bind_param($bindStr, ...array_values($this->parameters));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get()
    {
        $result = $this->execute()->get_result();
        // 处理结果集
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // 处理每一行结果
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 获取第一条结果
     */
    public function first()
    {
        $this->limit(1);
        $result = $this->execute()->get_result();
        return $result->fetch_assoc();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function insert($data)
    {
        try {
            $columns          = implode(', ', array_keys($data));
            $placeholders     = implode(', ', array_fill(0, count($data), '?'));
            $this->query      = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $this->parameters = array_values($data);
            // return $this->execute()->affected_rows;
            return $this->execute()->insert_id;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return false|mixed
     */
    public function insertGetId($data)
    {
        return $this->insert($data);
    }

    /**
     * @return mixed
     */
    public function getLastInsertedId()
    {
        return $this->conn->insert_id;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return !empty($this->conn->error) ? $this->conn->error : $this->error;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function update($data)
    {
        try {
            $set              = implode(' = ?, ', array_keys($data)) . ' = ?';
            $this->query      = "UPDATE {$this->table} SET $set";
            $this->query      .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $parameters       = array_values($data);
            $this->parameters = empty($this->parameters) ? $parameters : array_merge($parameters, $this->parameters);
            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * @param array  $data
     * @param string $byColumnField
     *
     * @return mixed
     */
    public function batchUpdate(array $data, string $byColumnField = 'id')
    {
        try {
            $this->parameters = [];

            // 先获取所有要更新的字段
            $firstRow    = $data[0];
            $columnField = [];
            foreach ($firstRow as $itemField => $itemValue) {
                if ($itemField == $byColumnField) {
                    continue;
                }
                $columnField[] = $itemField;
            }
            $updateFieldsData = [];

            // 获取 $byColumnField 列的值作为关联数组的键
            $keys = array_column($data, $byColumnField);

            foreach ($columnField as $fieldName) {
                // 将 $byColumnField 列的值与 $fieldName 列的值关联起来创建新的关联数组
                $updateFieldsData[$fieldName] = array_combine($keys, array_column($data, $fieldName));
            }

            $set = [];
            foreach ($updateFieldsData as $field => $columnData) {
                $whenStr = '';
                foreach ($columnData as $keyVal => $value) {
                    $whenStr            .= "WHEN $byColumnField = $keyVal THEN ? ";
                    $this->parameters[] = $value;
                }
                $set[] = "$field = CASE $whenStr END";
            }

            $this->query = "UPDATE {$this->table} SET " . implode(", ", $set);
            $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';

            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $column
     * @param $amount
     *
     * @return mixed
     */
    public function increment($column, $amount = 1)
    {
        $this->query = "UPDATE {$this->table} SET $column = $column + $amount";
        $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
        return $this->execute()->affected_rows;
    }

    /**
     * @param $column
     * @param $amount
     *
     * @return mixed
     */
    public function decrement($column, $amount = 1)
    {
        return $this->increment($column, -$amount);
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        if (empty($this->whereStr) && empty($this->limitStr)) {
            $this->throwErr('删除数据时必须使用 where() 或 limit() 方法, 否则会清空表数据');
        }
        try {
            $this->query = "DELETE FROM {$this->table}";
            $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $this->query .= $this->limitStr ? "LIMIT {$this->limitStr} " : '';
            return $this->execute()->affected_rows;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $isolationLevel
     *
     * @return mixed
     */
    public function setTransactionIsolation($isolationLevel)
    {
        $this->query("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        return $this;
    }

    /**
     * @param $sql
     */
    public function exec($sql = '')
    {
        return (bool)$this->query($sql);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public function quote($string)
    {
        return $this->conn->real_escape_string($string);
    }

    /**
     * @return mixed
     */
    public function beginTransaction()
    {
        $this->conn->autocommit(false);
        // return $this->conn->begin_transaction();
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        $res = $this->conn->commit();
        // 恢复自动提交
        $this->conn->autocommit(true);
        return $res;
    }

    /**
     * @return mixed
     */
    public function rollback()
    {
        $res = $this->conn->rollback();
        // 恢复自动提交
        $this->conn->autocommit(true);
        return $res;
    }

    /**
     * 事务状态查询
     */
    public function inTransaction()
    {
        // mysqli 不支持事务状态查询，只能通过判断是否开启了
        return false;
    }

    /**
     * 获取数据表字段信息
     */
    public function getColumns()
    {
        $this->query      = "DESCRIBE {$this->table}";
        $this->parameters = [];
        return $this->execute()->get_result();
    }

    /**
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->conn->last_query;
    }

    /**
     * 遍历查询结果的方法
     *
     * @param $callback
     *
     * @return mixed
     * @throws Exception
     */
    public function each($callback)
    {
        $stmt   = $this->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $callback($row);
        }
        $stmt->close();
        return $this;
    }

    /**
     * 获取数据表主键列名
     */
    public function getPrimaryKey()
    {
        // 使用 SHOW KEYS 查询主键信息
        $this->query      = "SHOW KEYS FROM {$this->table} WHERE Key_name = 'PRIMARY'";
        $this->parameters = [];
        $primaryKeyRows   = $this->execute()->get_result();
        // 获取结果集中的主键列名
        $primaryKeyColumnNames = [];
        foreach ($primaryKeyRows as $row) {
            $primaryKeyColumnNames[] = $row['Column_name'];
        }
        return $primaryKeyColumnNames;
    }

    /**
     * 获取数据表索引列信息
     */
    public function getIndexes()
    {
        $this->query      = "SHOW INDEX FROM {$this->table}";
        $this->parameters = [];
        $indexes          = $this->execute()->get_result();

        // 获取结果集中的索引列名
        $indexColumnNames = [];
        foreach ($indexes as $row) {
            $indexColumnNames[] = $row['Column_name'];
        }
        return $indexColumnNames;
    }

    // 添加获取单个值的聚合查询方法
    public function aggregate($function, $column)
    {
        try {
            $this->fieldStr = "$function($column)";
            empty($this->query) && $this->toSql();
            $result = $this->execute()->get_result();
            $row    = $result->fetch_assoc();
            return (int)current($row);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
