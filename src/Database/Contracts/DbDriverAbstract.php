<?php

namespace zxf\Database\Contracts;

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

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'mysqli';

    // 是否将绑定参数转换为问号参数
    protected bool $convertBindParamsToQuestionMarks = true;

    // sql 生成器
    protected SqlBuildGenerator $sqlBuildGenerator;

    // database 连接信息
    protected $conn;

    // 错误信息
    protected string $error = '';

    // 连接配置信息
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

        if (empty($options) || empty($options['host']) || empty($options['dbname']) || empty($options['username']) || !isset($options['password'])) {
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
     * 获取第一条结果
     */
    public function first()
    {
        $this->sqlBuildGenerator->limit(1);
        $result = $this->runSql($this->sqlBuildGenerator->buildQuery(), $this->sqlBuildGenerator->getBindings());
        return $this->dataProcessing($result);
    }
}