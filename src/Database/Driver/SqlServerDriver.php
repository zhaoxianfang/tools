<?php

namespace zxf\Database\Driver;

use Exception;
use PDO;
use PDOException;

class SqlServerDriver extends PdoDriver
{
    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'sqlserver';

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'sqlsrv';

    /**
     * 配置 驱动连接数据库的实现
     *
     * @param array  $options        连接参数, 包含 host、db_name、username、password 等
     * @param string $connectionName 连接名称, 主要针对框架
     *
     * @throws Exception
     */
    public function connect(array $options = [],string $connectionName = 'default'): static
    {
        try {
            $this->getConfig($options, $connectionName);

            // 构建DSN字符串
            $dsn = "sqlsrv:Server={$this->config['host']},{$this->config['port']};Database={$this->config['database']}";

            // PDO连接参数
            // 连接SQLite数据库，传递连接参数
            $this->conn = new PDO($dsn,$this->config['username'], $this->config['password']);
            // 设置字符集
            $this->conn->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            // 连接失败
            throw new Exception('连接失败：' . $e->getCode() . ' => ' . $e->getMessage());
        }
        return $this;
    }
}