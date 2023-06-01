<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */

    'namespace'                      => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | 加载模块 Modules 模块路由时候 是否根据路由文件名 自动加载 `\App\Http\Kernel::class`->$middlewareGroups 里面 存在的 中间件
    |--------------------------------------------------------------------------
    |
    | 默认开启
    |
    */
    'auto_use_middleware_groups'     => true,

    /*
    |--------------------------------------------------------------------------
    | 加载模块 Modules 模块路由时候 xxx.php 文件里面的路由需要自动添加上同名 `xxx`前缀和 `xxx.` 路由命名 的路由文件
    |--------------------------------------------------------------------------
    |
    | 默认['api'] 表示 api.php 里面的路由全部加上`api`前缀和 `api.` 路由命名
    |
    */
    'route_need_add_prefix_and_name' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Modules 模块下的 config.php 文件是否发布到 系统config 文件夹下
    |--------------------------------------------------------------------------
    |
    | 默认关闭
    |
    */
    'publishes_config'               => false,

    /*
    |--------------------------------------------------------------------------
    | Modules 模块下的 views 文件是否发布到 系统 /resources/views/ 文件夹下
    |--------------------------------------------------------------------------
    |
    | 默认开启
    |
    */
    'publishes_views'                => true,

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */
    'stubs'                          => [
        'files'        => [
            'routes/web'      => 'Routes/web.php',
            'routes/api'      => 'Routes/api.php',
            'views/index'     => 'Resources/views/index.blade.php',
            'views/master'    => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
        ],
        'replacements' => [
            'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api'      => ['LOWER_NAME'],
            'webpack'         => ['LOWER_NAME'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer'        => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
            ],
        ],
        'gitkeep'      => false,
    ],

    'paths'      => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path used for save the generated module. This path also will be added
        | automatically to list of scanned folders.
        |
        */

        'modules' => base_path('Modules'),
        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules assets path.
        |
        */

        'assets'    => public_path('modules'),

        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator' => [
            'config'          => ['path' => 'Config', 'generate' => true],
            'command'         => ['path' => 'Console', 'generate' => false],
            'migration'       => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder'          => ['path' => 'Database/Seeders', 'generate' => false],
            'factory'         => ['path' => 'Database/factories', 'generate' => false],
            'model'           => ['path' => 'Entities', 'generate' => true],
            'routes'          => ['path' => 'Routes', 'generate' => true],
            'controller'      => ['path' => 'Http/Controllers/Web', 'generate' => true],
            'filter'          => ['path' => 'Http/Middleware', 'generate' => false],
            'request'         => ['path' => 'Http/Requests', 'generate' => true],
            'provider'        => ['path' => 'Providers', 'generate' => false],
            'assets'          => ['path' => 'Resources/assets', 'generate' => false],
            'lang'            => ['path' => 'Resources/lang', 'generate' => false],
            'views'           => ['path' => 'Resources/views', 'generate' => true],
            'test'            => ['path' => 'Tests/Unit', 'generate' => false],
            'test-feature'    => ['path' => 'Tests/Feature', 'generate' => false],
            'repository'      => ['path' => 'Repositories', 'generate' => false],
            'event'           => ['path' => 'Events', 'generate' => false],
            'listener'        => ['path' => 'Listeners', 'generate' => false],
            'policies'        => ['path' => 'Policies', 'generate' => false],
            'rules'           => ['path' => 'Rules', 'generate' => false],
            'jobs'            => ['path' => 'Jobs', 'generate' => false],
            'emails'          => ['path' => 'Emails', 'generate' => false],
            'notifications'   => ['path' => 'Notifications', 'generate' => false],
            'resource'        => ['path' => 'Http/Resources', 'generate' => false],
            'component-view'  => ['path' => 'Resources/views/components', 'generate' => false],
            'component-class' => ['path' => 'View/Components', 'generate' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache'      => [
        'enabled'  => false,
        'key'      => 'laravel-modules',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, file, database etc. The only
    | required parameter is 'class'.
    | The file activator will store the activation status in storage/installed_modules
    */
    'activators' => [
        'file' => [
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],
];
