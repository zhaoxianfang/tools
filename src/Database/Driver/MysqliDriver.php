<?php

namespace zxf\Database\Driver;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use zxf\Database\Contracts\DbDriverAbstract;

class MysqliDriver extends DbDriverAbstract
{
    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'mysql';

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'mysqli';

    // 是否将绑定参数转换为问号参数
    protected bool $convertBindParamsToQuestionMarks = true;

    /**
     * 配置 驱动连接数据库的实现
     *
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、db_name、username、password 等
     *
     * @throws Exception
     */
    public function connect(array $options = [], string $connectionName = 'default')
    {
        $params = $this->getConfig($options, $connectionName);

        try {
            $mysqlIc    = new ReflectionClass($this->extensionName);
            $this->conn = $mysqlIc->newInstanceArgs($params);
            if ($this->conn->connect_error) {
                $this->error = "连接失败: " . $this->conn->connect_error;
                throw new Exception($this->error);
            }
            // 设置字符集为utf8mb4
            $this->conn->prepare("SET NAMES utf8mb4");
            return $this;
        } catch (Exception $e) {
            throw new Exception("Database 连接失败：" . $e->getMessage());
        }
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->conn->close();
    }

    /**
     * 执行$sql直接 「查询」
     *
     * @param string $sql sql语句
     *
     * @return array
     * @throws Exception
     */
    public function query(string $sql)
    {
        // 执行查询
        $result = $this->conn->query($sql);
        // 检查查询是否成功
        if ($result) {
            // 处理结果集
            $data = [];
            // 遍历数据
            while ($row = $result->fetch_assoc()) {
                // 处理每一行结果
                $data[] = $row;
            }
            // 释放结果集
            $result->close();
            return $data;
        } else {
            // 释放结果集
            $result->close();
            throw new Exception("执行查询时发生错误: " . $this->conn->error);
        }

    }

    /**
     * 直接执行$sql语句的实现
     *
     * @param string     $sql        sql语句
     * @param array|null $bindParams 绑定参数
     *
     * @return mixed
     * @throws Exception
     */
    public function runSql(string $sql = '', array|null $bindParams = null): mixed
    {
        $sql        = empty($sql) ? $this->sqlGenerator->buildQuery() : $sql;
        $bindParams = is_null($bindParams) ? $this->sqlGenerator->getBindings() : $bindParams;
        $this->writeRunSql($sql, $bindParams);
        // 准备 SQL 语句
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
        }

        // 绑定参数
        $bindStr = '';
        foreach ($bindParams as $value) {
            //"i": 表示整数类型
            //"d": 表示双精度浮点数类型
            //"s": 表示字符串类型
            //"b": 表示二进制数据类型（例如 BLOB）
            $bindStr .= is_numeric($value) ? 'd' : 's';
        }
        $stmt->bind_param($bindStr, ...array_values($bindParams));

        // 执行 SQL 语句
        $stmt->execute();
        if ($stmt->error) {
            // 关闭连接
            $stmt->close();
            throw new Exception("ERROR: " . $stmt->errno . ':' . $stmt->error);
        }

        // 关闭连接
        // $stmt->close();
        // 返回结果集
        return $stmt;
    }

    /**
     * 各个驱动实现自己的数据处理
     *
     * @param mixed $resource $stmt 资源
     *
     * @return array
     */
    public function dataProcessing(mixed $resource): array
    {
        // 获取结果
        // $resource->get_result(); // 获取查询结果集
        // $resource->fetch_assoc(); // 遍历每行数据：返回关联数组，其中数组的键是列名，值是对应的数据
        // $resource->fetch_array(); // 遍历每行数据：返回关联数组，返回同时包含数字索引和关联键的数组
        // $resource->fetch_row(); // 遍历每行数据：返回关联数组，返回数字索引的数组、不关心列名、索引从0开始

        // $resource->affected_rows; // 获取受影响的行数 （对于 INSERT, UPDATE, DELETE 等操作）
        // $resource->field_count;   // 返回结果集中的列数
        // $resource->insert_id;   // 返回最后一个 INSERT 操作生成的 AUTO_INCREMENT 值
        // $resource->num_rows;   // 对于 SELECT 查询，返回结果集中的行数。但请注意，对于未缓冲的查询，这个值可能不可用或不准确
        // $resource->param_count;   // 返回预处理语句中的参数数量

        $result = $resource->get_result();
        // 处理结果集
        $data = [];
        // 遍历数据
        while ($row = $result->fetch_assoc()) {
            // 处理每一行结果
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 插入数据, 返回受影响的行数
     */
    public function insert(array $data)
    {
        $this->sqlGenerator->create($data);
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    /**
     * 插入数据, 返回插入的id
     */
    public function insertGetId(array $data)
    {
        $this->sqlGenerator->create($data);
        $stmt = $this->runSql();
        return $stmt->insert_id ?? 0;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->conn->errno . ':' . $this->conn->error;
    }

    /**
     * 更新数据
     */
    public function update(array $data)
    {
        $this->sqlGenerator->update($data);
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    /**
     * 设置批量更新
     *  如果库中 $uniqueColumn 的字段(单个或者多个字段联合)值存在，则更新 $updateColumn字段 ，否则创建$data中的数据
     *
     * 重要提示：
     *          1、批量更新的字段值或多个字段组合必须是唯一的，否则会出现更新失败
     *          2、$uniqueColumn 和 $updateColumn 的字段值必须在 $data 中存在
     *          3、【强烈建议】$uniqueColumn 和 $updateColumn 的字段合在一起刚好是 $data 中的「所有」字段
     *
     *
     * @param array $data         需要更新或插入的数据； eg: [
     *                            ['column1'=>'val_1_0', 'column2'=>'val_2_0', 'unique_column'=>'unique_val_0'],
     *                            ['column1'=>'val_1_1','column2'=>'val_2_1', 'unique_column'=>'unique_val_1']
     *                            ]
     * @param array $uniqueColumn 根据$uniqueColumn里的字段组合的值进行判断，如果存在则更新$updateColumn里的字段，否则创建一条新数据 eg:  ['unique_column']
     *                            或 ['column1', 'column2']
     * @param array $updateColumn 需要更新的字段 eg: ['column1', 'column2'] 或 ['column2']
     *
     */
    public function upsert(array $data = [], array $uniqueColumn = [], array $updateColumn = [])
    {
        $this->sqlGenerator->upsert($data, $uniqueColumn, $updateColumn);
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    public function increment(string $column, int $amount = 1)
    {
        $this->sqlGenerator->update([$column => "`{$column}` + $amount"]);
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    public function decrement(string $column, int $amount = 1)
    {
        $this->sqlGenerator->update([$column => "`{$column}` - $amount"]);
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $this->sqlGenerator->delete();
        $stmt = $this->runSql();
        return $stmt->affected_rows ?? 0;
    }

    /**
     * 清除查询条件和参数
     */
    public function reset()
    {
        $this->sqlGenerator->reset();
        return $this;
    }

    public function each($callback, string $sql = '', ?array $bindParams = null)
    {
        if ($callback instanceof Closure && is_callable($callback)) {
            $stmt   = $this->runSql($sql, $bindParams);
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $callback($row);
            }
            $stmt->close();
            return $this;
        }
        throw new Exception("参数必须是闭包函数");
    }

    /**
     * 聚合查询
     */
    public function aggregate(string $aggregate = 'count', string $column = 'id')
    {
        $function = strtolower($aggregate);
        if (!in_array($function, ['count', 'max', 'min', 'avg', 'sum', 'exists', 'doesntExist'])) {
            throw new Exception("不支持的聚合查询");
        }
        $this->sqlGenerator->$function($column);
        $stmt = $this->runSql();
        // 获取结果集
        $result = $stmt->get_result();
        $stmt->close();

        if ($row = $result->fetch_assoc()) {
            // 返回$row中的第一个键的值
            return array_values($row)[0];
        } else {
            return null;
        }
    }

    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        $this->conn->autocommit(false);
        // return $this->conn->begin_transaction();
        return $this;
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->conn->commit();
        // 恢复自动提交
        $this->conn->autocommit(true);
        return $this;
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        $this->conn->rollback();
        // 恢复自动提交
        $this->conn->autocommit(true);
        return $this;
    }
}