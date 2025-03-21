<?php

namespace zxf\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pagination\Paginator;
use zxf\Laravel\BuilderQuery\Builder as WhereHasInBuilder;
use zxf\Laravel\Modules\Activators\FileActivator;
use zxf\Laravel\Modules\Contracts;
use zxf\Laravel\Modules\Laravel;
use zxf\Laravel\Modules\Middleware\ExtendMiddleware;
use zxf\Laravel\Modules\Providers\AutoLoadModulesProviders;
use zxf\Laravel\Modules\Providers\ConsoleServiceProvider;
use zxf\Laravel\Modules\Providers\ContractsServiceProvider;
use zxf\Laravel\Modules\Providers\ModulesRouteServiceProvider;
use zxf\Laravel\Trace\Handle;
use zxf\Laravel\Trace\ToolsParseExceptionHandler;
use zxf\TnCode\Providers\TnCodeValidationProviders;

/**
 * 支持 laravel 服务注入
 * Class ServiceProvider.
 */
class LaravelModulesServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function __construct($app)
    {
        is_laravel() && ! empty(config('modules.enable', true)) && parent::__construct($app);
    }

    public function register()
    {
        if (! is_laravel() || empty(config('modules.enable', true))) {
            return;
        }

        $this->mergeConfigFrom(__DIR__.'/../../config/modules.php', 'modules');

        // 注册modules 模块服务
        $this->registerModulesServices();

        $this->registerProviders();

        // 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
        WhereHasInBuilder::register($this);
    }

    public function boot()
    {
        if (! is_laravel() || empty(config('modules.enable', true))) {
            return;
        }

        $this->registerMiddleware(ExtendMiddleware::class);

        $this->bootPublishes();

        // 加载模块boot
        $this->mapModuleBoot();

        // 加载debug路由
        $this->loadRoutesFrom(__DIR__.'/Trace/routes/debugger.php');
        // 加载tncode 路由
        $this->loadRoutesFrom(__DIR__.'/../TnCode/routes.php');

        // 处理异常
        // 获取 Laravel 的异常处理器实例
        $handler = app(ExceptionHandler::class);

        // 自定义的异常处理
        app()->bind(ExceptionHandler::class, function () use ($handler) {
            return new ToolsParseExceptionHandler($handler);
        });

        // 设置数据分页模板
        $this->setPaginationView();
        // 使用提示
        $this->tips();
    }

    /**
     * 注册中间件
     *
     * @param  string  $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $this->app['router']->aliasMiddleware('exception.handler', $middleware);

        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app[\Illuminate\Foundation\Http\Kernel::class];
        $kernel->pushMiddleware($middleware); // 追加
        // $kernel->prependMiddleware($middleware); // 放在最前面
        // if (isset($kernel->getMiddlewareGroups()['web'])) {
        //     $kernel->appendMiddlewareToGroup('web', $middleware); // 追加
        //     // $kernel->prependMiddlewareToGroup('web', $middleware);   // 放在最前面
        // }
    }

    // 设置数据分页模板
    protected function setPaginationView()
    {
        // php artisan vendor:publish --tag=laravel-pagination
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
    }

    // 加载发布文件
    protected function bootPublishes()
    {
        // 把config 文件夹类的配置文件 发布到 config 文件夹下
        $this->publishes([
            __DIR__.'/../../config/' => config_path(''),
        ], 'modules');

        // 发布Modules模块文件组
        $this->publishes([
            __DIR__.'/../../publishes/' => base_path(''),
        ], 'modules');
    }

    protected function registerModulesServices()
    {
        $this->app->singleton(Contracts\RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new Laravel\LaravelFileRepository($app, $path);
        });
        $this->app->singleton(Contracts\ActivatorInterface::class, function ($app) {
            return new FileActivator($app);
        });

        $this->app->alias(Contracts\RepositoryInterface::class, 'modules');

        // 定义 app('trace')
        $this->app->singleton(Handle::class, function ($app) {
            return new Handle($app);
        });
        $this->app->alias(Handle::class, 'trace');
    }

    /**
     * Register providers.
     */
    protected function registerProviders()
    {
        if (is_dir(base_path(modules_name()))) {
            $this->app->register(ConsoleServiceProvider::class);
        }
        $this->app->register(ContractsServiceProvider::class);

        // 注册自定义 中间件 tools_middleware
        // $this->app->singleton(ExtendMiddleware::class);
        // $this->app->alias(ExtendMiddleware::class, 'tools_middleware');

        // 注册路由
        $this->app->register(ModulesRouteServiceProvider::class);
        // 自动加载 Providers 服务提供者
        AutoLoadModulesProviders::start($this->app);

        // 自动加载TnCode 验证器
        $this->app->register(TnCodeValidationProviders::class);

        // 注册异常报告 [注册异常后，会替代laravel 自身的 错误机制] 不推荐
        // set_error_handler('exception_handler');
    }

    protected function mapModuleBoot()
    {
        if (! is_dir(base_path(modules_name()))) {
            return false;
        }
        $modules = array_slice(scandir(base_path(modules_name())), 2);
        foreach ($modules as $module) {
            $moduleLower = strtolower($module);
            if (is_dir(base_path(modules_name().'/'.$module))) {
                $this->registerTranslations($module, $moduleLower);
                $this->registerConfig($module, $moduleLower);
                $this->registerViews($module, $moduleLower);
                if (is_dir(module_path($module, 'Database/Migrations'))) {
                    $this->loadMigrationsFrom(module_path($module, 'Database/Migrations'));
                }
            }
        }
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations($module, $moduleLower)
    {
        $langPath = resource_path('lang/modules/'.$moduleLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $moduleLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            if (is_dir(module_path($module, 'Resources/lang'))) {
                $this->loadTranslationsFrom(module_path($module, 'Resources/lang'), $moduleLower);
                $this->loadJsonTranslationsFrom(module_path($module, 'Resources/lang'));
            }
        }
    }

    /**
     * Register views.
     * 然后就可以使用 view('demo::test') 去访问 Demo/Resources/views里面的视图文件了
     *
     * @return void
     */
    public function registerViews($module, $moduleLower)
    {
        $viewPath = resource_path('views/modules/'.$moduleLower);

        $sourcePath = module_path($module, 'Resources/views');
        if (! is_dir($sourcePath)) {
            return;
        }
        if (config('modules.publishes_views', true)) {
            $this->publishes([
                $sourcePath => $viewPath,
            ], ['views', $moduleLower.'-module-views']);
        }
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module, $moduleLower), [$sourcePath]), $moduleLower);

        $this->loadViewsFrom(module_path($module, 'Resources/views'), $moduleLower);
    }

    private function getPublishableViewPaths($module, $moduleLower): array
    {
        $paths = [];
        foreach (app('config')->get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$moduleLower)) {
                $paths[] = $path.'/modules/'.$moduleLower;
            }
        }

        return $paths;
    }

    /**
     * 注册 config.
     *
     * @return void
     */
    protected function registerConfig($module, $moduleLower)
    {
        if (is_dir(module_path($module, 'Config'))) {
            $configs = array_slice(scandir(module_path($module, 'Config')), 2); // 2:表示从数组的第[2]项取，即不包含 . 和 ..
            foreach ($configs as $file) {
                // 获取完整文件路径
                $fullPath = module_path($module, 'Config/'.$file);
                if (is_file($fullPath) && str_ends_with($fullPath, '.php')) {
                    $filename = pathinfo($fullPath, PATHINFO_FILENAME);
                    if (config('modules.publishes_config', false)) {
                        // config.php 文件 发布成 $moduleLower.php ,其他文件 发布成 $moduleLower/$filename.php
                        $this->publishes([
                            $fullPath => config_path($moduleLower.($filename == 'config' ? '' : '/'.$filename).'.php'),
                        ], 'config');
                    }
                    // 读取配置文件的分隔符(config.php 文件直接使用模块名小写,针对其他文件生效)
                    $configDelimiter = config('modules.multi_config_delimiter', '_');
                    $this->mergeConfigFrom(
                        module_path($module, 'Config/'.$filename.'.php'), $moduleLower.($filename == 'config' ? '' : $configDelimiter.$filename)
                    );
                }
            }
        }
    }

    // 使用多模块提示
    protected function tips()
    {
        if (app()->runningInConsole() && is_laravel() && ! is_dir(base_path(modules_name()))) {
            echo PHP_EOL;
            echo '======================================================================================================'.PHP_EOL;
            echo '  ███████╗██╗  ██╗███████╗    ████████╗ ██████╗  ██████╗ ██╗     ███████╗  '.PHP_EOL;
            echo '  ╚══███╔╝╚██╗██╔╝██╔════╝    ╚══██╔══╝██╔═══██╗██╔═══██╗██║     ██╔════╝  '.PHP_EOL;
            echo '    ███╔╝  ╚███╔╝ █████╗         ██║   ██║   ██║██║   ██║██║     ███████╗  '.PHP_EOL;
            echo '   ███╔╝   ██╔██╗ ██╔══╝         ██║   ██║   ██║██║   ██║██║     ╚════██║  '.PHP_EOL;
            echo '  ███████╗██╔╝ ██╗██║            ██║   ╚██████╔╝╚██████╔╝███████╗███████║  '.PHP_EOL;
            echo '  ╚══════╝╚═╝  ╚═╝╚═╝            ╚═╝    ╚═════╝  ╚═════╝ ╚══════╝╚══════╝  '.PHP_EOL;
            echo '======================================================================================================'.PHP_EOL;
            echo ' 插    件 | composer require zxf/tools '.PHP_EOL;
            echo ' 格    言 | 人生在勤，不索何获 '.PHP_EOL;
            echo ' 模块发布 | php artisan vendor:publish --provider="zxf\Laravel\LaravelModulesServiceProvider" '.PHP_EOL;
            echo ' 文档地址 | https://weisifang.com/docs/2 '.PHP_EOL;
            echo ' github   | https://github.com/zhaoxianfang/tools '.PHP_EOL;
            echo ' gitee    | https://gitee.com/zhaoxianfang/tools '.PHP_EOL;
            echo '======================================================================================================'.PHP_EOL;
        }
    }
}
