<?php

namespace zxf\Database\Contracts;

use Closure;
use zxf\Database\Generator\SqlBuildGenerator;
use Exception;

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
    protected SqlBuildGenerator $sqlBuildGenerator;

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
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、dbname、username、password 等
     *
     * @throws Exception
     */
    public function __construct(string $connectionName = 'default', array $options = [])
    {
        if (empty($this->extensionName) || !extension_loaded($this->extensionName)) {
            throw new Exception('不支持的扩展:' . ($this->extensionName ?: '未知扩展'));
        }

        $this->sqlBuildGenerator = new SqlBuildGenerator();
        $this->sqlBuildGenerator->setConvertBindParamsToQuestionMarks($this->convertBindParamsToQuestionMarks);

        $this->connect($connectionName, $options);
    }

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery()
    {
        return new static();
    }

    /**
     * 获取配置信息
     *
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、dbname、username、password 等
     *
     * @return array
     * @throws Exception
     */
    protected function getConfig(string $connectionName = 'default', array $options = []): array
    {

        if (empty($options) || empty($options['host']) || !isset($options['dbname']) || !isset($options['username']) || !isset($options['password'])) {
            if (!function_exists('config') || empty($options = config('tools_database.' . $this->driverName . '.' . $connectionName))) {
                throw new Exception('Database配置有误');
            }
        }

        $this->config = [
            'hostname' => $options['host'],
            'username' => $options['username'] ?? 'root',
            'password' => $options['password'] ?? '',
            'database' => $options['dbname'] ?? '',
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
            return call_user_func_array(array($this, $method), ...$arg);
        }

        return call_user_func_array(array($this->sqlBuildGenerator, $method), ...$arg);
    }

    /**
     * 调用静态方法
     */
    public static function __callStatic(string $method, ...$arg)
    {
        throw new Exception('不支持的静态方法:' . $method);
    }

    // ================================================
    // 以下是可通用的查询构造器的方法
    // ================================================

    /**
     * 获取所有结果
     */
    public function get()
    {
        $result = $this->runSql($this->sqlBuildGenerator->buildQuery(), $this->sqlBuildGenerator->getBindings());
        return $this->dataProcessing($result);
    }

    /**
     * 获取一条结果
     */
    public function find()
    {
        $this->sqlBuildGenerator->limit(1);
        $result = $this->runSql($this->sqlBuildGenerator->buildQuery(), $this->sqlBuildGenerator->getBindings());
        return $this->dataProcessing($result);
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
}