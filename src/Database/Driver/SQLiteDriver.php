<?php

namespace zxf\Database\Driver;

use Exception;
use PDO;
use PDOException;

class SQLiteDriver extends PdoDriver
{
    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'sqlite';

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'sqlite';

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

            // 其他连接参数
            $connectionOptions = [
                PDO::ATTR_TIMEOUT => 10, // 设置超时时间为10秒
            ];

            // PDO连接参数
            // 连接SQLite数据库，传递连接参数
            $this->conn = new PDO("sqlite:{$this->config['host']}", null, null, $connectionOptions);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // 设置错误模式
        } catch (PDOException $e) {
            // 连接失败
            throw new Exception('连接失败：' . $e->getCode() . ' => ' . $e->getMessage());
        }
        return $this;
    }
}