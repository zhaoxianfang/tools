<?php

namespace zxf\Database\Driver;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use zxf\Database\Contracts\DbDriverAbstract;

class PdoDriver extends DbDriverAbstract
{
    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'mysql';

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'pdo';

    // 是否将绑定参数转换为问号参数
    protected bool $convertBindParamsToQuestionMarks = false;

    /**
     * 配置 驱动连接数据库的实现
     *
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、dbname、username、password 等
     *
     * @throws Exception
     */
    public function connect(string $connectionName = 'default', array $options = [])
    {
        try {
            $this->getConfig($connectionName, $options);
            // PDO连接参数
            $pdo        = new PDO("mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4");
            $pdoIc      = new \ReflectionClass($pdo);
            $this->conn = $pdoIc->newInstanceArgs($this->config);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // 连接失败
            throw new Exception('连接失败：' . $e->getCode() . ' => ' . $e->getMessage());
        }

        return $this;

    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->conn = null;
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
        $stmt = $this->conn->query($sql);
        if ($stmt === false) {
            $this->error = '查询失败: ' . $this->conn->errorInfo()[2];
            throw new Exception($this->error);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public function runSql(string $sql = '', ?array $bindParams = null): mixed
    {
        $sql        = empty($sql) ? $this->sqlBuildGenerator->buildQuery() : $sql;
        $bindParams = is_null($bindParams) ? $this->sqlBuildGenerator->getBindings() : $bindParams;

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            $this->error = '预处理失败: ' . $this->conn->errorInfo()[2];
            throw new Exception($this->error);
        }

        $result = $stmt->execute($bindParams);
        if ($result === false) {
            $this->error = '执行失败: ' . $stmt->errorInfo()[2];
            throw new Exception($this->error);
        }
        return $stmt;
    }

    /**
     * 各个驱动实现自己的数据处理
     *
     * @param mixed $resource 资源
     *
     * @return array
     */
    public function dataProcessing(mixed $resource): array
    {
        if ($resource instanceof PDOStatement) {
            return $resource->fetchAll(PDO::FETCH_ASSOC);
        }
        return $resource;
    }

    /**
     * 判断是否存在
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * 判断是否 不存在
     */
    public function doesntExist()
    {
        // TODO: Implement doesntExist() method.
    }

    /**
     * 插入数据
     */
    public function insert(array $data)
    {
        // TODO: Implement insert() method.
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        // TODO: Implement getError() method.
    }

    /**
     * 更新数据
     */
    public function update(array $data)
    {
        // TODO: Implement update() method.
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
        // TODO: Implement upsert() method.
    }

    public function increment(string $column, int $amount = 1)
    {
        // TODO: Implement increment() method.
    }

    public function decrement(string $column, int $amount = 1)
    {
        // TODO: Implement decrement() method.
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * 清除查询条件和参数
     */
    public function reset()
    {
        // TODO: Implement reset() method.
    }

    public function each($callback)
    {
        // TODO: Implement each() method.
    }

    /**
     * 获取结果数量
     */
    public function count(string $column = 'id')
    {
        // TODO: Implement count() method.
    }

    public function max(string $column)
    {
        // TODO: Implement max() method.
    }

    public function min(string $column)
    {
        // TODO: Implement min() method.
    }

    public function avg(string $column)
    {
        // TODO: Implement avg() method.
    }

    public function sum(string $column)
    {
        // TODO: Implement sum() method.
    }
}