<?php

namespace zxf\Database\Driver\Mysql;

use \Closure;
use \Exception;
use \PDO;
use \PDOException;
use zxf\Database\Driver\MySQLAbstract;

class PdoDriver extends MySQLAbstract
{

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct($connectionName = 'default', ...$args)
    {
        if (!extension_loaded('pdo')) {
            throw new Exception('不支持的扩展:pdo');
        }
        $this->connect('default', ...$args);
    }

    /**
     * 配置 连接数据库
     */
    public function connect($connectionName = 'default', ...$args)
    {
        if (!$this->getConfig($connectionName, ...$args)) {
            return false;
        }

        if (!extension_loaded('pdo')) {
            throw new Exception('不支持的扩展:pdo');
        }

        try {
            $pdoIc      = new \ReflectionClass('pdo');
            $this->conn = $pdoIc->newInstanceArgs($this->config);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new Exception("Database 连接失败：" . $e->getMessage());
        }
        return $this;
    }

    private function getConfig($connectionName = 'default', ...$args)
    {
        if (empty($args) || !is_array($config = $args[0]) || count($config) < 4 || empty($config['host']) || empty($config['dbname']) || empty($config['username']) || !isset($config['password'])) {
            if (!function_exists('config') || empty($config = config('tools_database.mysql.' . $connectionName))) {
                return false;
            }
        }

        $dns          = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->config = [$dns, $config['username'], $config['password'] ?? ''];
        return $this->config;
    }


    /**
     * 执行查询
     */
    public function execute()
    {
        try {
            empty($this->query) && $this->toSql();
            $stmt = $this->conn->prepare($this->query);
            $stmt->execute($this->parameters);
            $this->stmt = $stmt;
            $this->reset();
            return $stmt;
        } catch (PDOException $e) {
            $this->reset();
            throw new Exception("数据库错误：" . $e->getMessage());
        }
    }

    /**
     * 获取所有结果
     */
    public function get()
    {
        return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取第一条结果
     */
    public function first()
    {
        return $this->execute()->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * 插入数据
     */
    public function insert($data)
    {
        try {
            $columns          = implode(', ', array_keys($data));
            $placeholders     = implode(', ', array_fill(0, count($data), '?'));
            $this->query      = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $this->parameters = array_values($data);
            $this->execute();
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 插入数据并获取ID
     */
    public function insertGetId($data)
    {
        $res = $this->insert($data);
        return $res ? $this->getLastInsertedId() : null;
    }

    /**
     * 获取上一次插入的ID
     *
     * @return false|string
     */
    public function getLastInsertedId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 更新数据
     */
    public function update($data)
    {
        try {
            $set              = implode(' = ?, ', array_keys($data)) . ' = ?';
            $this->query      = "UPDATE {$this->table} SET $set";
            $this->query      .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
            $parameters       = array_values($data);
            $this->parameters = empty($this->parameters) ? $parameters : array_merge($parameters, $this->parameters);
            $stmt             = $this->execute();
            return $stmt->rowCount();

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * 批量更新记录
     *
     * @param array  $data          被跟新的数据二维数组
     * @param string $byColumnField 根据$data中的哪个字段来更新
     *
     * @return false|int 返回影响的行数，false表示更新失败, int表示影响的行数
     *              返回false时，调用用 ->getError() 方法获取错误信息
     *
     * eg:  $updateData = [
     *          ['id' => 2, 'username' => 'username2-multi', 'nickname' => 'nickname2-multi'],
     *          ['id' => 3, 'username' => 'username3-multi', 'nickname' => 'nickname3-multi'],
     *          // 添加更多的更新数据项
     *      ];
     *      $db->table('test')->batchUpdate($updateData,'id');
     *
     * @throws Exception
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

            $stmt = $this->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    // 添加更新自增操作
    public function increment($column, $amount = 1)
    {
        $this->query = "UPDATE {$this->table} SET $column = $column + $amount";
        $this->query .= $this->whereStr ? " WHERE {$this->whereStr} " : '';
        $stmt        = $this->execute();
        return $stmt->rowCount();
    }

    // 添加更新自减操作
    public function decrement($column, $amount = 1)
    {
        return $this->increment($column, -$amount);
    }

    /**
     * 删除数据
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
            $stmt        = $this->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    // 添加设置事务隔离级别的方法
    public function setTransactionIsolation($isolationLevel)
    {
        $this->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        return $this;
    }


    // 执行一条 SQL 语句，并返回受影响的行数
    public function exec($sql = '')
    {
        return $this->conn->exec($sql);
    }

    // 添加防止 SQL 注入功能(为SQL语句中的字符串添加引号)
    public function quote($string)
    {
        return $this->conn->quote($string);
    }

    // 开启事务的方法
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
        return $this;
    }

    // 添加提交事务的方法
    public function commit()
    {
        $this->conn->commit();
        return $this;
    }

    // 添加回滚事务的方法
    public function rollback()
    {
        $this->conn->rollback();
        return $this;
    }


    // 检测是否在一个事务内
    public function inTransaction()
    {
        return $this->conn->inTransaction();
    }


    /**
     * 获取数据表字段信息
     */
    public function getColumns()
    {
        $this->query      = "DESCRIBE {$this->table}";
        $this->parameters = [];
        return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取最后执行的查询语句
     */
    public function getLastQuery()
    {
        // 获取最后一次查询的SQL语句
        return $this->stmt->queryString;
    }

    // 遍历查询结果的方法
    public function each($callback)
    {
        $stmt = $this->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $callback($row);
        }
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
        $primaryKeyRows   = $this->execute()->fetchAll(PDO::FETCH_ASSOC);
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
        $indexes          = $this->execute()->fetchAll(PDO::FETCH_ASSOC);

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
            return $this->execute()->fetchColumn();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    public function getParams()
    {
        //获取sql预处理命令
        return $this->stmt->debugDumpParams();
    }
}
