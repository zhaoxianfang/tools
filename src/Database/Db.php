<?php

namespace zxf\Database;

use Exception;
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
    protected static string $connection = 'default';

    /**
     * 实例
     */
    protected static Db $instance;

    public string $error = ''; // 异常信息

    /**
     *  驱动器映射
     */
    private array $driverMap = [
        'mysql' => PdoDriver::class,
        'mysqli' => MysqliDriver::class,
        'pgsql' => PgSqlDriver::class,
        'sqlite' => SQLiteDriver::class,
        'sqlsrv' => SqlServerDriver::class,
    ];

    /**
     * 构造函数，初始化数据库连接
     *
     * @param  string  $driverName  驱动器名称 支持: mysql、mysqli、pgsql、sqlite、sqlsrv
     * @param  array  $args  数据库连接参数
     * @param  string  $connection  数据库连接名称 例如: default
     *
     * @throws Exception
     */
    public function __construct(string $driverName = '', array $args = [], string $connection = '')
    {
        $defaultConfig = config('tools_database.default');
        self::$driverName = ! empty($driverName) ? $driverName : $defaultConfig['driver'];
        self::$connection = ! empty($connection) ? $connection : $defaultConfig['connection'];

        if (isset($this->driverMap[self::$driverName])) {
            self::$driver = new $this->driverMap[self::$driverName](self::$connection, $args);
        } else {
            throw new Exception('不支持的数据库驱动');
        }
    }

    /**
     * 重新示例化
     *
     * @param  string  $connection  数据库连接名称 例如: default
     *
     * @throws Exception
     */
    public static function connection(string $connection = ''): Db|static
    {
        // 不做缓存单例、每次都重新实例化
        // if (!isset(self::$instance) || empty(self::$instance)) {
        self::$instance = new static('', [], $connection);

        // }
        return self::$instance;
    }

    /**
     * 调用不存在的方法时
     */
    public function __call(string $method, mixed $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arg);
        }

        return call_user_func_array([self::$driver, $method], $arg);
    }

    /**
     * 静态调用
     */
    public static function __callStatic(string $method, mixed $arg)
    {
        $class = self::class;
        if (method_exists($class, $method)) {
            return call_user_func_array([$class, $method], $arg);
        }

        return call_user_func_array([self::$driver, $method], $arg);
    }
}
