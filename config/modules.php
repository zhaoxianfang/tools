<?php

use zxf\Laravel\Modules\Activators\FileActivator;

return [

    /*
    |--------------------------------------------------------------------------
    | 是否启用 Modules
    | Enable Modules plugins
    |--------------------------------------------------------------------------
    |
    | Default true
    |
    */
    'enable' => true,

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
    | 加载 Modules 模块里面的路由的时候 ，是否根据路由文件名 自动加载 `\App\Http\Kernel::class`->$middlewareGroups 里面 存在的 中间件
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
    | 默认关闭
    |
    */
    'publishes_views'                => false,

    /*
    |--------------------------------------------------------------------------
    | 是否开启 Trace 页面调试
    |--------------------------------------------------------------------------
    |
    | 默认关闭
    |
    */
    'trace'                          => (bool)env('APP_TRACE', false),

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
            'routes/web'         => 'Routes/web.php',
            'routes/api'         => 'Routes/api.php',
            'views/index'        => 'Resources/views/index.blade.php',
            'views/master'       => 'Resources/views/layouts/master.blade.php',
            'scaffold/config'    => 'Config/config.php',
            // 自定义本地化
            'lang/en/messages'   => 'Resources/lang/en/messages.php',
            'lang/en/validation' => 'Resources/lang/en/validation.php',
        ],
        'replacements' => [
            'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'routes/api'      => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'vite'            => ['LOWER_NAME', 'STUDLY_NAME'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME', 'LOWER_NAME'],
            'composer'        => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
                'APP_FOLDER_NAME',
            ],
        ],
        'gitkeep'      => true,
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

        'assets'     => public_path('modules'),

        /*
        |--------------------------------------------------------------------------
        | The migrations' path
        |--------------------------------------------------------------------------
        |
        | Where you run the 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */
        'migration'  => base_path('Database/Migrations'),

        /*
        |--------------------------------------------------------------------------
        | The app path
        |--------------------------------------------------------------------------
        |
        | app folder name
        | for example can change it to 'src' or 'App'
        */
        'app_folder' => 'app/',

        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator'  => [
            // app/
            'actions'         => ['path' => 'Actions', 'generate' => false],
            'casts'           => ['path' => 'Casts', 'generate' => false],
            'channels'        => ['path' => 'Broadcasting', 'generate' => false],
            'class'           => ['path' => 'Classes', 'generate' => false],
            'command'         => ['path' => 'Console', 'generate' => false],
            'component-class' => ['path' => 'View/Components', 'generate' => false],
            'emails'          => ['path' => 'Emails', 'generate' => false],
            'event'           => ['path' => 'Events', 'generate' => false],
            'enums'           => ['path' => 'Enums', 'generate' => false],
            'exceptions'      => ['path' => 'Exceptions', 'generate' => false],
            'jobs'            => ['path' => 'Jobs', 'generate' => false],
            'helpers'         => ['path' => 'Helpers', 'generate' => false],
            'interfaces'      => ['path' => 'Interfaces', 'generate' => false],
            'listener'        => ['path' => 'Listeners', 'generate' => false],
            'model'           => ['path' => 'Models', 'generate' => true],
            'notifications'   => ['path' => 'Notifications', 'generate' => false],
            'observer'        => ['path' => 'Observers', 'generate' => false],
            'policies'        => ['path' => 'Policies', 'generate' => false],
            'provider'        => ['path' => 'Providers', 'generate' => true],
            'repository'      => ['path' => 'Repositories', 'generate' => false],
            'resource'        => ['path' => 'Transformers', 'generate' => false],
            'route-provider'  => ['path' => 'Providers', 'generate' => false],
            'rules'           => ['path' => 'Rules', 'generate' => false],
            'services'        => ['path' => 'Services', 'generate' => false],
            'scopes'          => ['path' => 'Models/Scopes', 'generate' => false],
            'traits'          => ['path' => 'Traits', 'generate' => false],

            // app/Http/
            'controller'      => ['path' => 'Http/Controllers/Web', 'generate' => true],
            'filter'          => ['path' => 'Http/Middleware', 'generate' => false],
            'request'         => ['path' => 'Http/Requests', 'generate' => false],

            // config/
            'config'          => ['path' => 'Config', 'generate' => true],

            // database/
            'factory'         => ['path' => 'Database/Factories', 'generate' => false],
            'migration'       => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder'          => ['path' => 'Database/Seeders', 'generate' => false],

            // lang/ 本地化
            'lang'            => ['path' => 'Resources/lang', 'generate' => false],

            // resource/
            'assets'          => ['path' => 'Resources/assets', 'generate' => true],
            'component-view'  => ['path' => 'Resources/views/components', 'generate' => false],
            'views'           => ['path' => 'Resources/views', 'generate' => true],

            // routes/ 路由
            'routes'          => ['path' => 'Routes', 'generate' => true],

            // tests/
            'test-feature'    => ['path' => 'Tests/Feature', 'generate' => false],
            'test-unit'       => ['path' => 'Tests/Unit', 'generate' => false],
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
        'enabled'  => env('MODULES_CACHE_ENABLED', false),
        'driver'   => env('MODULES_CACHE_DRIVER', 'file'),
        'key'      => env('MODULES_CACHE_KEY', 'laravel-modules'),
        'lifetime' => env('MODULES_CACHE_LIFETIME', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, file, database, etc. The only
    | required parameter is 'class'.
    | The file activator will store the activation status in storage/installed_modules
    */
    'activators' => [
        'file' => [
            'class'          => FileActivator::class,
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],

    'activator' => 'file',
];
