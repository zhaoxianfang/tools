<?php
// 数据库相关的配置，mysql、redis、elastic 等
return [
    'redis' => [
        'default' => [
            'host'    => env('TOOLS_REDIS_HOST', ''),
            'port'    => env('TOOLS_REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('TOOLS_REDIS_AUTH', ''),
        ],
    ],
    //  mysql
    'mysql' => [
        'default' => [
            'host'     => env('TOOLS_MYSQL_HOST', '127.0.0.1'),
            'username' => env('TOOLS_MYSQL_HOST', 'root'),
            'password' => env('TOOLS_MYSQL_HOST', ''),
            'db'       => env('TOOLS_MYSQL_HOST', 'test'),
            'port'     => env('TOOLS_MYSQL_HOST', 3306),
            // 'prefix'   => env('TOOLS_MYSQL_HOST', ''),
            'charset'  => env('TOOLS_MYSQL_HOST', 'utf8mb4'),
            'socket'   => env('TOOLS_MYSQL_SOCKET', null),
        ],
    ],
];