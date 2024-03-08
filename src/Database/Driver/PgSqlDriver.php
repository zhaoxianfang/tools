<?php

namespace zxf\Database\Driver;

class PgSqlDriver extends PdoDriver
{
    /**
     * 需要在 tools_database 中配置的数据库连接名称
     *
     * @var string 数据库驱动名称 支持: mysql、pgsql、sqlite、sqlserver、oracle
     */
    protected string $driverName = 'pgsql';

    // 连接数据库的驱动扩展名称 eg: mysqli、pdo 等
    protected string $extensionName = 'pgsql';

}