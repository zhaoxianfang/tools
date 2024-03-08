<?php

namespace zxf\Database;

use \Exception;

use zxf\Database\Contracts\DbDriverInterface;
use zxf\Database\Driver\MysqliDriver;
use zxf\Database\Driver\PdoDriver;
use zxf\Database\Driver\PgSqlDriver;
use zxf\Database\Driver\SQLiteDriver;
use zxf\Database\Driver\SqlServerDriver;

class Db
{
    /**
     * @var DbDriverInterface 驱动器
     */
    protected static DbDriverInterface $driver;

    // 驱动器名称
    /**
     * 驱动器名称 支持: mysql、mysqli、pgsql、sqlite、sqlsrv
     */
    protected static string $driverName = 'mysql';
    /**
     * @var string 数据库连接名称
     */
    protected static string $connectionName = 'default';

    /**
     * 实例
     *
     * @var Db
     */
    protected static Db $instance;

    public string $error = ''; // 异常信息

    /**
     *  驱动器映射
     */
    private array $driverMap = [
        'mysql'  => PdoDriver::class,
        'mysqli' => MysqliDriver::class,
        'pgsql'  => PgSqlDriver::class,
        'sqlite' => SQLiteDriver::class,
        'sqlsrv' => SqlServerDriver::class,
    ];

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct(string $driverName = '', $connectionName = '', array $args = [])
    {
        $defaultConfig        = config('tools_database.default');
        self::$driverName     = !empty($driverName) ? $driverName : $defaultConfig['driver'];
        self::$connectionName = !empty($connectionName) ? $connectionName : $defaultConfig['connection'];

        if (isset($this->driverMap[self::$driverName])) {
            self::$driver = new $this->driverMap[self::$driverName](self::$connectionName, $args);
        } else {
            throw new Exception('不支持的数据库驱动');
        }
    }

    public static function instance(string $driverName = '', $connectionName = '', array $args = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($driverName, $connectionName, $args);
        }
        return self::$instance;
    }

    public function getInstance()
    {
        return self::instance();
    }

    /**
     * 调用不存在的方法时
     */
    public function __call(string $method, mixed $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), ...$arg);
        }

        return call_user_func_array(array(self::$driver, $method), ...$arg);
    }

    /**
     * 静态调用
     */
    public static function __callStatic(string $method, mixed $arg)
    {
        $class = self::class;
        if (method_exists($class, $method)) {
            return call_user_func_array(array($class, $method), ...$arg);
        }

        return call_user_func_array(array(self::$driver, $method), ...$arg);
    }
}
