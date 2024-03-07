<?php

namespace zxf\Database;

use \Exception;

use zxf\Database\Contracts\MysqlInterface;
use zxf\Database\Driver\Mysql\PdoDriver;
use zxf\Database\Driver\Mysql\MysqliDriver;

class Db
{
    // 驱动器 MysqlInterface
    protected $driver;
    // 驱动器名称
    protected static $driverName = 'pdo';

    /**
     * 实例
     *
     * @var Db
     */
    protected static $instance;

    /**
     * mysql 实例列表
     *
     * @var array [profile_name=>MysqlInterface]
     */
    protected $mysqlInstance = [];

    /**
     * 连接设置
     *
     * @var array connections settings [profile_name=>[hostname,username,...,charset]]
     */
    protected $connectSettings = [];

    /**
     *  默认（主）mysqli连接的名称
     *
     * @var string the name of a default (main) mysqli connection
     */
    public $defConnectionName = 'default';

    /**
     * 当前连接mysql的句柄
     */
    public static $activeConnectionObj;
    public static $activeConnectionName = 'default';

    public $activeConfig = []; // 当前使用的连接配置信息

    public $error = ''; // 异常信息

    // 默认配置
    private $config = [
        'host'     => '127.0.0.1',
        'dbname'   => 'test',
        'username' => 'root',
        'password' => '',
        'port'     => 3306,
        'socket'   => null,
        'charset'  => 'utf8mb4',
    ];

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct($connectionName = 'default', ...$args)
    {
        if (self::$driverName == 'pdo' && extension_loaded('pdo')) {
            $this->driver = PdoDriver::class;
        }
        if (self::$driverName == 'mysqli' && extension_loaded('mysqli')) {
            $this->driver = MysqliDriver::class;
        }
        if (empty($this->driver)) {
            throw new Exception('未配置MySQL扩展 pdo 或 mysqli');
        }
        if ($this->getConfig($connectionName, ...$args)) {
            $this->addConnection($this->defConnectionName, $this->activeConfig);
        }
    }

    public static function instance($driverName = 'pdo', $connectionName = 'default', ...$args)
    {
        if (is_null(self::$instance)) {
            self::$driverName = $driverName ?? 'pdo';
            self::$instance = new static($connectionName, ...$args);
        }
        return self::$instance;
    }

    public function getInstance()
    {
        return self::instance();
    }

    public function reset()
    {
        self::$activeConnectionObj->reset();
        $this->connect();
        return $this;
    }

    private function getConfig($connectionName = 'default', ...$args)
    {
        if (
            empty($args) || !is_array($config = $args[0]) || count($config) < 4
            || empty($config['host']) || empty($config['dbname']) || empty($config['username']) || !isset($config['password'])
        ) {
            if (!function_exists('config') || empty($config = config('tools_database.mysql.' . $connectionName))) {
                return false;
            }
        }

        $config = array_merge($this->config, $config);

        $this->activeConfig = $config;
        return $config;
    }

    /**
     * Create & store at _mysqli new mysqli instance
     *
     * @param string $name
     * @param array  $params
     *
     * @return $this
     * @throws Exception
     */
    public function addConnection(string $name, array $params = [])
    {
        if (!is_subclass_of($this->driver, MysqlInterface::class)) {
            throw new Exception("数据库驱动异常!");
        }
        if (empty($params)) {
            throw new Exception("Database 配置异常!");
        }
        $this->connectSettings[$name] = $params;
        return $this;
    }

    public function connect($connectName = 'default')
    {
        if (!isset($this->connectSettings[$connectName])) {
            throw new Exception('未设置连接配置文件');
        }
        if (!empty($this->mysqlInstance) && !empty($connectDriver = $this->mysqlInstance[$connectName]) && is_object($connectDriver)) {
            self::$activeConnectionObj  = $connectDriver;
            self::$activeConnectionName = $connectName;
            return $this;
        }

        $params                            = $this->connectSettings[$connectName];
        $driverObj                         = new $this->driver($connectName, $params);
        $this->mysqlInstance[$connectName] = $driverObj;
        self::$activeConnectionObj         = $driverObj;
        self::$activeConnectionName        = $connectName;
        return $this;
    }

    public function disconnect($connection = 'default')
    {
        if (!isset($this->mysqlInstance[$connection])) {
            return;
        }
        $this->mysqlInstance[$connection]->close();
        unset($this->mysqlInstance[$connection]);
    }

    /**
     * Catches calls to undefined methods.
     *
     * Provides magic access to private functions of the class and native public Db functions
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }
        if (empty(self::$activeConnectionObj)) {
            $this->connect();
        }

        return call_user_func_array(array(self::$activeConnectionObj, $method), $arg);
    }

    /**
     * Catches calls to undefined static methods.
     *
     * Transparently creating Model class to provide smooth API like name::get() name::orderBy()->get()
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic(string $method, $arg)
    {
        $class = self::class;
        if (method_exists($class, $method)) {
            return call_user_func_array(array($class, $method), ...$arg);
        }

        if (empty(self::$activeConnectionObj)) {
            $class = new static;
            $class->connect();
        }
        return call_user_func_array(array($class::$activeConnectionObj, $method), ...$arg);
    }
}
