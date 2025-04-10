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
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | 允许自动加载的中间件组
    |--------------------------------------------------------------------------
    |
    | laravel 默认内置了 web 和 api 两个中间件组，如果要使用其他中间件组，需要手动添加到该数组中；eg: ['web', 'api','admin']
    |
    */
    'allow_automatic_load_middleware_groups' => ['web', 'api', 'admin'],

    /*
    |--------------------------------------------------------------------------
    | 加载 Modules 模块里面的路由的时候 ，是否根据路由文件名 自动加载 `\App\Http\Kernel::class`->$middlewareGroups 里面 存在的 中间件
    |--------------------------------------------------------------------------
    |
    | 默认开启
    |
    */
    'auto_use_middleware_groups'             => true,

    /*
    |--------------------------------------------------------------------------
    | 加载模块 Modules 模块路由时候 xxx.php 文件里面的路由需要自动添加上同名 `xxx`前缀和 `xxx.` 路由命名 的路由文件
    |--------------------------------------------------------------------------
    |
    | 默认['api'] 表示 api.php 里面的路由全部加上`api`前缀和 `api.` 路由命名, 不需要就设置为 []
    |
    */
    'route_need_add_prefix_and_name'         => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Modules 模块下的 config.php 文件是否发布到 系统config 文件夹下
    |--------------------------------------------------------------------------
    |
    | 默认关闭
    |
    */
    'publishes_config'                       => false,

    /*
    |--------------------------------------------------------------------------
    | Modules 模块下的 views 文件是否发布到 系统 /resources/views/ 文件夹下
    |--------------------------------------------------------------------------
    |
    | 默认关闭
    |
    */
    'publishes_views'                        => false,

    /*
    |--------------------------------------------------------------------------
    | 是否开启 Trace 页面调试
    |--------------------------------------------------------------------------
    |
    | 默认关闭
    |
    */
    'trace'                                  => (bool)env('APP_DEBUG', false),


    /*
    |--------------------------------------------------------------------------
    | 开启 Trace 时, 自定义处理 Trace 调试产生的数据
    |--------------------------------------------------------------------------
    |
    | 默认为空
    |    例如:
    |    'trace_end_handle_class' => \App\Services\TraceEndService::class,
    |    // 表示在 TraceEndHandle 类中接管 Trace 调试产生的数据
    |
    |    use Illuminate\Support\Facades\Log;
    |
    |    class TraceEndService
    |    {
    |        public function handle(array $trace=[]): void
    |        {
    |            // 做点什么...
    |            // Log::channel('stack')->debug('===== [Trace]调试: ===== ', $trace);
    |        }
    |    }
    |
    */
    'trace_end_handle_class'                 => '',

    /*
    |--------------------------------------------------------------------------
    | 一个模块下有多个配置文件时(Config文件夹下)，定义配置文件名分隔符
    |--------------------------------------------------------------------------
    | config.php 配置文件不使用分割符，直接使用模块名小写读取配置
    |           eg: Modules/模块/Config/config.php 直接使用 config('模块') 读取 config.php 文件的配置
    |
    | 分隔符默认为_,
    |    Modules/模块/Config/aha.php 使用 config('模块_aha') 读取 aha.php 文件的配置
    | 若定义分隔符为点(.),
    |    Modules/模块/Config/aha.php 使用 config('模块.aha') 读取 aha.php 文件的配置
    |
    | [特别注意]: 如果使用点号「.」分割符时
    |    若config.php 文件中定义了 name 配置,又在config.php同级文件夹下定义了 name.php 配置，那么读取
    |    config('demo模块.name') 时因为config.php文件内的name键会和name.php文件配置冲突导致报错
    |    因此：配置此项时要避免出现冲突的情况
    |
    */
    'multi_config_delimiter'                 => '_',

    /*
    |--------------------------------------------------------------------------
    | 代码追踪调试使用的编辑器
    |--------------------------------------------------------------------------
    |
    | 设置代码调试编辑器，调试工具会引导点击链接跳转到编辑器的指定位置，默认为 phpstorm
    |
    | Supported: "phpstorm", "vscode", "vscode-insiders", "vscode-remote",
    |            "vscode-insiders-remote", "vscodium", "textmate", "emacs",
    |            "sublime", "atom", "nova", "macvim", "idea", "netbeans",
    |            "xdebug", "espresso"
    |
    */
    'editor'                                 => env('TRACE_EDITOR') ?: env('TRACE_EDITOR', 'phpstorm'),

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */
    'stubs' => [
        'files' => [
            'routes/web' => 'routes/web.php',
            'routes/api' => 'routes/api.php',
            'views/index' => 'resources/views/index.blade.php',
            'views/master' => 'resources/views/layouts/master.blade.php',
            'scaffold/config' => 'config/config.php',
            // 自定义本地化
            'lang/en/messages' => 'Resources/lang/en/messages.php',
            'lang/zh_CN/messages' => 'Resources/lang/zh_CN/messages.php',
            'lang/zh_CN' => 'Resources/lang/zh_CN.json',
        ],
        'replacements' => [
            /**
             * 为每个部分定义自定义替换.
             *
             * 提示: Keys 应该全是大写.
             */
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME', 'PLURAL_LOWER_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'routes/api' => ['LOWER_NAME', 'STUDLY_NAME', 'PLURAL_LOWER_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'views/index' => ['LOWER_NAME'],
            'views/master' => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME'],
            'scaffold/config' => ['STUDLY_NAME', 'LOWER_NAME'],
            'lang/en/messages' => ['STUDLY_NAME', 'LOWER_NAME'],
            'lang/zh_CN/messages' => ['STUDLY_NAME', 'LOWER_NAME'],
        ],
        'gitkeep' => true,
    ],
    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path is used to save the generated module.
        | This path will also be added automatically to the list of scanned folders.
        |
        */
        'modules' => base_path('Modules'),

        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules' assets path.
        |
        */
        'assets' => public_path('modules'),

        /*
        |--------------------------------------------------------------------------
        | The migrations' path
        |--------------------------------------------------------------------------
        |
        | Where you run the 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */
        'migration' => base_path('database/migrations'),

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
        | Setting the generate key to false will not generate that folder
        */
        'generator' => [
            // app/
            'model' => ['path' => 'Models', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],

            'actions' => ['path' => 'Actions', 'generate' => false],
            'casts' => ['path' => 'Casts', 'generate' => false],
            'channels' => ['path' => 'Broadcasting', 'generate' => false],
            'class' => ['path' => 'Classes', 'generate' => false],
            'command' => ['path' => 'Console', 'generate' => false],
            'component-class' => ['path' => 'View/Components', 'generate' => false],
            'emails' => ['path' => 'Emails', 'generate' => false],
            'event' => ['path' => 'Events', 'generate' => false],
            'enums' => ['path' => 'Enums', 'generate' => false],
            'exceptions' => ['path' => 'Exceptions', 'generate' => false],
            'jobs' => ['path' => 'Jobs', 'generate' => false],
            'helpers' => ['path' => 'Helpers', 'generate' => false],
            'interfaces' => ['path' => 'Interfaces', 'generate' => false],
            'listener' => ['path' => 'Listeners', 'generate' => false],
            'notifications' => ['path' => 'Notifications', 'generate' => false],
            'observer' => ['path' => 'Observers', 'generate' => false],
            'policies' => ['path' => 'Policies', 'generate' => false],
            'repository' => ['path' => 'Repositories', 'generate' => false],
            'resource' => ['path' => 'Transformers', 'generate' => false],
            'route-provider' => ['path' => 'Providers', 'generate' => false],
            'rules' => ['path' => 'Rules', 'generate' => false],
            'services' => ['path' => 'Services', 'generate' => false],
            'scopes' => ['path' => 'Models/Scopes', 'generate' => false],
            'traits' => ['path' => 'Traits', 'generate' => false],

            // app/Http/
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],

            'filter' => ['path' => 'Http/Middleware', 'generate' => false],
            'request' => ['path' => 'Http/Requests', 'generate' => false],

            // config/
            'config' => ['path' => 'Config', 'generate' => true],

            // database/
            'factory' => ['path' => 'Database/Factories', 'generate' => true],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],

            // lang/
            'lang' => ['path' => 'Resources/lang', 'generate' => false],

            // resource/
            'assets' => ['path' => 'Resources/assets', 'generate' => false],
            'views' => ['path' => 'Resources/views', 'generate' => true],

            'component-view' => ['path' => 'Resources/views/components', 'generate' => false],

            // routes/
            'routes' => ['path' => 'Routes', 'generate' => true],

            // tests/
            'test-feature' => ['path' => 'Tests/Feature', 'generate' => false],
            'test-unit' => ['path' => 'Tests/Unit', 'generate' => false],
        ],
    ],
];
