<?php

namespace zxf\Database\Driver;

use Closure;
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

    /**
     * 连接数据库的驱动扩展名称 eg: mysql、pgsql 等
     * 默认使用mysql扩展
     */
    protected string $extensionName = 'mysql';

    /**
     * @var bool 是否将绑定参数转换为问号参数
     */
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
            // eg: $dsn = "mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4";
            // eg: $dsn = "pgsql:host=localhost;port=5432;dbname=test;user=postgres;password=123456";
            $dsn        = "{$this->extensionName}:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4";
            $pdoIc      = new \ReflectionClass('pdo');
            $this->conn = $pdoIc->newInstanceArgs([$dsn, $this->config['username'], $this->config['password']]);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // 设置错误模式
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
        // 检查查询是否成功
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
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
    public function runSql(string $sql = '', ?array $bindParams = null): mixed
    {
        $sql        = empty($sql) ? $this->sqlBuildGenerator->buildQuery() : $sql;
        $bindParams = is_null($bindParams) ? $this->sqlBuildGenerator->getBindings() : $bindParams;
        try {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                $this->error = '预处理失败: ' . $this->conn->errorInfo()[2];
                throw new Exception($this->error);
            }

            // 绑定一个 PHP 变量到预处理语句的一个命名参数，并指定其数据类型。
            // $stmt->bindParam(':paramName', $variable, PDO::PARAM_INT);

            // 传入绑定参数
            $stmt->execute($bindParams);
            return $stmt;
        } catch (PDOException $e) {
            // 执行失败
            throw new Exception('执行失败：' . $e->getCode() . ' => ' . $e->getMessage());
        }
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
        // $stmt->fetchAll(PDO::FETCH_ASSOC); // 获取结果集中的所有记录，并返回一个关联数组的数组
        // $stmt->fetch(PDO::FETCH_ASSOC); // 获取结果集中的下一条记录，并返回一个关联数组。如果没有更多记录，返回 false。

        // $stmt->fetchAll(PDO::FETCH_NUM); // 返回索引数组
        // $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // 获取第一列的所有值
        // $stmt->fetchColumn(); // 查询只返回单一值（例如 SELECT COUNT(*) FROM table）

        // $stmt->getColumnMeta($columnIndex); // 获取指定列的元数据（如列名、数据类型等）。

        // $stmt->rowCount(); // 获取受影响的行数，对于 SELECT 语句，这通常是结果集中的行数

        // 设置默认的获取结果模式。这会影响后续的 fetch() 或 fetchAll() 调用
        // $stmt->setFetchMode(PDO::FETCH_ASSOC); // 设置默认获取模式为关联数组

        // $lastInsertId = $stmt->lastInsertId(); // 获取最近一次 INSERT 操作生成的 ID。


        // $stmt->closeCursor(); // 关闭预处理语句的游标，释放与之关联的资源，使语句能再次被执行


        // 使用 while 循环遍历结果集中的每一行记录。
        // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //     // 在这里处理每一行数据
        // }

        // 检查 $stmt 是否是一个有效的 PDOStatement 对象。
        if ($resource instanceof PDOStatement) {
            return $resource->fetchAll(PDO::FETCH_ASSOC);
        }
        return $resource;
    }

    /**
     * 插入数据
     */
    public function insert(array $data)
    {
        $this->sqlBuildGenerator->create($data);
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
    }

    /**
     * 插入数据, 返回插入的id
     */
    public function insertGetId(array $data)
    {
        $this->sqlBuildGenerator->create($data);
        $this->runSql();
        return $this->conn->lastInsertId();
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->conn->errorCode() . ':' . $this->conn->errorInfo()[2];
    }

    /**
     * 更新数据
     */
    public function update(array $data)
    {
        $this->sqlBuildGenerator->update($data);
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
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
        $this->sqlBuildGenerator->upsert($data, $uniqueColumn, $updateColumn);
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
    }

    public function increment(string $column, int $amount = 1)
    {
        $this->sqlBuildGenerator->update([$column => "`{$column}` + $amount"]);
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
    }

    public function decrement(string $column, int $amount = 1)
    {
        $this->sqlBuildGenerator->update([$column => "`{$column}` - $amount"]);
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $this->sqlBuildGenerator->delete();
        $stmt = $this->runSql();
        return $stmt->rowCount() ?? 0;
    }

    /**
     * 清除查询条件和参数
     */
    public function reset()
    {
        $this->sqlBuildGenerator->reset();
        return $this;
    }

    public function each($callback)
    {
        if ($callback instanceof Closure && is_callable($callback)) {
            $stmt = $this->runSql();

            // 使用 while 循环遍历结果集中的每一行记录。
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $callback($row);
            }
            $stmt->closeCursor();
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
        $this->sqlBuildGenerator->$function($column);
        $stmt = $this->runSql();

        // 获取结果集中的单行数据
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 关闭语句和连接（可选，PHP 会在脚本结束时自动关闭）
        $stmt->closeCursor();

        // 返回$row中的第一个键的值
        return array_values($row)[0];
    }

    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        // 开始事务
        $this->conn->beginTransaction();
        return $this;
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        // 提交事务
        $this->conn->commit();
        return $this;
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        // 回滚事务
        $this->conn->rollBack();
        return $this;
    }
}