<?php
// ====================================================
// 数据库相关的配置，mysql、redis、elastic 等
// ====================================================
return [
    'default' => [
        'driver'     => 'mysql', // 默认数据库驱动名称，和下面default同级的键名对应，支持: mysql、pgsql、sqlite、sqlserver、oracle
        'connection' => 'default', // 默认连接名称
    ],
    'redis'   => [
        'default' => [
            'host'    => env('REDIS_HOST', ''),
            'port'    => env('REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('REDIS_PASSWORD', ''),
        ],
    ],
    //  mysql
    'mysql'   => [
        'default' => [
            'host'     => env('DB_HOST', '127.0.0.1'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'db_name'  => env('DB_DATABASE', 'test'),
            'port'     => env('DB_PORT', 3306),
            // 'prefix'   => env('DB_PREFIX', ''),
            'charset'  => env('DB_CHARSET', 'utf8mb4'),
            'socket'   => env('DB_SOCKET', null),
        ],
    ],
    //  sqlite
    'sqlite'  => [
        'default' => [
            // SQLite数据库文件路径
            'host'     => '',
            'username' => '',
            'password' => '',
            'db_name'  => '',
            'port'     => '',
            'charset'  => '',
            'socket'   => '',
        ],
    ],
];