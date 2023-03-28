<?php


namespace zxf\mysql;


use Exception;
use zxf\mysql\Contracts\MysqlInterface;
use zxf\mysql\Driver\MysqliDriver;

class Db
{
    // 驱动器 MysqlInterface
    protected $driver;

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
     * 当前连接mysqli的句柄
     *
     * @var mysqli
     */
    public static $activeConnectionObj;
    public static $activeConnectionName = 'default';

    public function __construct($hostname = null, $username = null, $password = null, $database = null, $port = 3306, $charset = 'utf8mb4', $socket = null)
    {
        if (extension_loaded('mysqli')) {
            $this->driver = MysqliDriver::class;
        }

        // 如果参数作为数组传递
        if (is_array($hostname) && !empty($hostname)) {
            foreach ($hostname as $key => $val) {
                $$key = $val;
            }
        }
        if (!empty($hostname) && !empty($username) && !empty($database)) {
            $options = [
                'hostname' => $hostname,
                'username' => $username,
                'password' => $password,
                'database' => $database,
                'port'     => !empty($port) ? $port : 3306,
                'socket'   => $socket,
                'charset'  => $charset,
            ];
        } else {
            $options = config('mysql.' . $this->defConnectionName);
        }
        $this->addConnection($this->defConnectionName, $options);
    }

    public static function instance($args = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }

    public function getInstance()
    {
        return self::instance();
    }

    public function reset(){
        $this->driver->reset();
        return $this;
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
        $params                       = !empty($params) ? $params : config('mysql.' . $name);
        $this->connectSettings[$name] = [];

        foreach (['hostname', 'username', 'password', 'database', 'port', 'socket', 'charset'] as $k) {
            $prm = isset($params[$k]) ? $params[$k] : null;
            if ($k == 'hostname') {
                if (is_object($prm)) {
                    $this->mysqlInstance[$name] = $prm;
                }
                if (!is_string($prm)) {
                    $prm = null;
                }
            }
            $this->connectSettings[$name][$k] = $prm;
        }
        return $this;
    }

    public function connect($connectName = 'default')
    {
        if (!isset($this->connectSettings[$connectName])) {
            throw new Exception('未设置连接配置文件');
        }
        $params                            = $this->connectSettings[$connectName];
        $mysqlIc                           = new \ReflectionClass($this->driver);
        $mysqli                            = $mysqlIc->newInstanceArgs($params);
        $this->mysqlInstance[$connectName] = $mysqli;
        self::$activeConnectionObj         = $mysqli;
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
     */
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        call_user_func_array(array(self::$activeConnectionObj, $method), $arg);
        return $this;
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
     */
    public static function __callStatic($method, $arg)
    {
        $obj    = self::instance();
        $result = call_user_func_array(array($obj::$activeConnectionObj, $method), $arg);
        if (method_exists($obj, $method)) {
            return $result;
        }
        return $obj;
    }
}