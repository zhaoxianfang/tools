<?php

namespace zxf\Database\Contracts;

use Closure;
use zxf\Database\Generator\SqlGenerator;
use Exception;
use PDO;
use zxf\Database\Model;
use zxf\Tools\Collection;

abstract class DbDriverAbstract implements DbDriverInterface
{

    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'mysql';

    /**
     * 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
     */
    protected string $extensionName = 'mysqli';

    /**
     * 是否将绑定参数转换为问号参数
     */
    protected bool $convertBindParamsToQuestionMarks = true;

    /**
     * sql 生成器
     */
    protected SqlGenerator $sqlGenerator;

    /**
     * database 连接信息
     */
    protected $conn;

    /**
     * 错误信息
     */
    protected string $error = '';

    /**
     * 连接配置信息
     */
    protected array $config = [];

    /**
     * 运行的sql 和绑定参数
     */
    protected array $runSqlList = [];

    private static Model $model;

    /**
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、db_name、username、password 等
     *
     * @throws Exception
     */
    public function __construct(string $connectionName = 'default', array $options = [])
    {
        // 检查扩展是否加载 mysql 使用的是 pdo 扩展实现
        $extensionName = $this->extensionName == 'mysql' ? 'pdo' : $this->extensionName;
        if (empty($this->extensionName) || !extension_loaded($extensionName)) {
            throw new Exception('不支持的扩展:' . ($extensionName ?: '未知扩展'));
        }

        $this->sqlGenerator = new SqlGenerator($this);
        $this->sqlGenerator->setConvertBindParamsToQuestionMarks($this->convertBindParamsToQuestionMarks);

        $this->connect($options, $connectionName);
    }

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery(): static
    {
        return new static();
    }

    /**
     * 设置调用的模型，仅用于 模型调用
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setModal(Model $model): self
    {
        self::$model = $model;
        return $this;
    }

    /**
     * 获取配置信息
     *
     * @param array  $options        连接参数, 包含 host、db_name、username、password 等
     * @param string $connectionName 连接名称,针对框架
     *
     * @return array
     * @throws Exception
     */
    protected function getConfig(array $options = [], string $connectionName = 'default'): array
    {

        if (empty($options) || empty($options['host']) || !isset($options['db_name']) || !isset($options['username']) || !isset($options['password'])) {
            if (!function_exists('config') || empty($options = config('tools_database.' . $this->driverName . '.' . $connectionName))) {
                throw new Exception('Database配置有误');
            }
        }

        $this->config = [
            'hostname' => $options['host'],
            'username' => $options['username'] ?? 'root',
            'password' => $options['password'] ?? '',
            'database' => $options['db_name'] ?? '',
            'port'     => $options['port'] ?? 3306,
            'socket'   => $options['socket'] ?? null,
            // 'charset'  => $config['charset'] ?? 'utf8mb4', // mysqli 中单独设置charset
        ];
        return $this->config;
    }

    /**
     * 调用一个方法
     */
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->sqlGenerator, $method), $arg);
    }

    /**
     * 调用静态方法
     */
    public static function __callStatic(string $method, $arg)
    {
        throw new Exception('不支持的静态方法:' . $method);
    }

    // ================================================
    // 以下是可通用的查询构造器的方法
    // ================================================

    /**
     * 填充插入和普通更新的数据
     */
    public function fill(array|Collection $data)
    {
        // 判断 $data 是否是 Collection
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }
        $this->sqlGenerator->fill($data);
        return $this;
    }

    /**
     * 获取所有结果
     */
    public function get()
    {
        $result = $this->runSql($this->sqlGenerator->buildQuery(), $this->sqlGenerator->getBindings());
        $data   = $this->dataProcessing($result);
        if (isset(self::$model) && !empty(self::$model)) {
            return self::$model->collect($data);
        }
        return $data;
    }

    /**
     * 获取一条结果
     */
    public function find()
    {
        $this->sqlGenerator->limit(1);
        $result = $this->runSql($this->sqlGenerator->buildQuery(), $this->sqlGenerator->getBindings());
        $data   = $this->dataProcessing($result);
        if (isset(self::$model) && !empty(self::$model)) {
            return self::$model->collect($data)->first();
        }
        return !empty($data) ? $data[0] : [];
    }

    /**
     * 判断是否存在
     */
    public function exists()
    {
        return (bool)$this->aggregate('exists', null);
    }

    /**
     * 判断是否 不存在
     */
    public function doesntExist()
    {
        return (bool)$this->aggregate('doesntExist', null);
    }

    /**
     * 获取结果数量
     */
    public function count(string $column = 'id')
    {
        return $this->aggregate('count', $column);
    }

    public function max(string $column)
    {
        return $this->aggregate('max', $column);
    }

    public function min(string $column)
    {
        return $this->aggregate('min', $column);
    }

    public function avg(string $column)
    {
        return $this->aggregate('avg', $column);
    }

    public function sum(string $column)
    {
        return $this->aggregate('sum', $column);
    }

    /**
     * 执行事务
     */
    public function transaction($callback)
    {
        if ($callback instanceof Closure && is_callable($callback)) {
            // 开始事务
            $this->beginTransaction();
            try {
                // 执行事务
                $callback($this);
                // 提交事务
                $this->commit();
            } catch (Exception $e) {
                // 回滚事务
                $this->rollback();
                throw $e;
            }
        } else {
            throw new Exception("参数必须是闭包函数");
        }
    }

    /**
     * 添加运行SQL语句和绑定参数
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return $this
     */
    public function writeRunSql(string $query = '', array $bindings = []): static
    {
        $this->runSqlList[] = [
            'run_time' => date('Y-m-d H:i:s'),
            'query'    => $query,
            'bindings' => $bindings,
        ];
        return $this;
    }

    /**
     * 获取最后一次运行的SQL语句和绑定参数
     *
     * @return array
     */
    public function getLastSql(): mixed
    {
        return end($this->runSqlList);
    }

    /**
     * 获取运行过的SQL语句和绑定参数
     *
     * @return mixed
     */
    public function getRunSql(): array
    {
        return $this->runSqlList;
    }

    /**
     * 清空运行的SQL语句和绑定参数
     *
     * @return $this
     */
    public function clearRunSql(): static
    {
        $this->runSqlList = [];
        return $this;
    }

    /**
     * 执行原生SQL语句
     *
     * @param string $sqlString
     * @param array  $bindings
     *
     * @return mixed
     * @throws Exception
     */
    public function execute(string $sqlString, array $bindings = []): mixed
    {
        $stmt = $this->runSql($sqlString, $bindings);
        return $stmt->rowCount() ?? 0;
    }

    /**
     * 添加索引【MySQL 版】
     *
     * @param string|array $column    索引列 eg: 'id', ['id', 'name']
     * @param string       $indexName 索引名称 eg: 'index_id'
     * @param string       $comment   索引注释 eg: '索引注释'
     * @param string       $indexType 索引类型 eg: 'FULLTEXT','NORMAL','SPATIAL','UNIQUE' 等 @see
     *                                https://dev.mysql.com/doc/refman/8.0/en/create-index.html
     * @param string       $indexFun  索引函数 eg: 'HASH','BTREE' @see
     *                                https://dev.mysql.com/doc/refman/8.0/en/create-index.html
     *
     * @return int|string
     * @throws Exception
     */
    public function addIndex(string|array $column, string $indexName = '', string $comment = '', string $indexType = '', string $indexFun = ''): int|string
    {
        $sql  = $this->sqlGenerator->buildAddIndexQuery($column, $indexName, $comment, $indexType);
        $stmt = $this->runSql($sql);
        return $stmt->rowCount() ?? 0;
    }

    /**
     * 删除 带顺序的字段组成的索引【MySQL 版】
     *
     * @param string|array $column 索引列 eg: 'title', ['title', 'status']
     *
     * @throws Exception
     */
    public function dropIndex(string|array $column): bool
    {
        $sql       = $this->sqlGenerator->buildIndexComposedOfQueryFieldsSQL($column);
        $tableName = $this->sqlGenerator->getTableName();
        $this->each(function ($item) use ($tableName) {
            if (!empty($item['INDEX_NAME'])) {
                $dropSql = "ALTER TABLE {$tableName} DROP INDEX " . $item['INDEX_NAME'];
                $this->runSql($dropSql, []);
            }
        }, $sql, []);
        return true;
    }
}