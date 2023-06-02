<?php
// 数据库相关的配置，mysql、redis、elastic 等
return [
    'redis' => [
        'default' => [
            'host'    => env('EXT_REDIS_HOST', ''),
            'port'    => env('EXT_REDIS_PORT', '6379'),
            'timeout' => env('EXT_REDIS_TIME_OUT', '5'),
            'auth'    => env('EXT_REDIS_AUTH', ''),
        ],
    ],
    //  mysql
    'mysql' => [
        'default' => [
            'host'     => env('EXT_MYSQL_HOST', '127.0.0.1'),
            'username' => env('EXT_MYSQL_HOST', 'root'),
            'password' => env('EXT_MYSQL_HOST', ''),
            'db'       => env('EXT_MYSQL_HOST', 'test'),
            'port'     => env('EXT_MYSQL_HOST', 3306),
            // 'prefix'   => env('EXT_MYSQL_HOST', ''),
            'charset'  => env('EXT_MYSQL_HOST', 'utf8mb4'),
            'socket'   => env('EXT_MYSQL_SOCKET', null),
        ],
    ],
];