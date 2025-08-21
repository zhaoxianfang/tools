<?php

namespace zxf\Laravel;

use Composer\InstalledVersions;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Console\AboutCommand;
use zxf\Laravel\BuilderQuery\Builder as WhereHasInBuilder;
use zxf\Laravel\Modules\Activators\FileActivator;
use zxf\Laravel\Modules\Contracts;
use zxf\Laravel\Modules\Laravel;
use zxf\Laravel\Modules\Middleware\ToolsMiddleware;
use zxf\Laravel\Modules\ModulesServiceProvider;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Trace\Handle;
use zxf\Laravel\Trace\ToolsParseExceptionHandler;

class LaravelModulesServiceProvider extends ModulesServiceProvider
{
    public function __construct($app)
    {
        is_laravel() && parent::__construct($app);
    }

    /**
     * Booting the package.
     */
    public function boot()
    {
        if (! is_laravel()) {
            return;
        }

        // 注册中间件
        $this->registerMiddleware(ToolsMiddleware::class);

        // 发布 配置文件
        $this->registerNamespaces();

        // 加载模块boot
        $this->registerModules();

        // 把 zxf-tools 添加到 about 命令中
        AboutCommand::add('zxf-tools', [
            'Version' => fn () => InstalledVersions::getPrettyVersion('zxf/tools'),
            'Docs' => fn () => 'https://weisifang.com/docs/2',
        ]);

        // 加载debug路由
        $this->loadRoutesFrom(__DIR__.'/Trace/routes/debugger.php');
        // 加载tncode 路由
        $this->loadRoutesFrom(__DIR__.'/../TnCode/routes.php');

        // 设置数据分页模板
        $this->setPaginationView();
        // 使用提示
        $this->tips();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        if (! is_laravel()) {
            return;
        }
        // 注册modules 模块服务
        $this->registerServices();
        $this->setupStubPath();
        // 注册服务
        $this->registerProviders();

        $this->mergeConfigFrom(__DIR__.'/../../config/modules.php', 'modules');


        // 处理 Laravel 异常
        // 方式一：单次注册
        $this->app->singleton(ExceptionHandler::class, function ($app) {
            // 获取原始处理器
            $originalHandler = $app->make(\Illuminate\Foundation\Exceptions\Handler::class);
            return new ToolsParseExceptionHandler($originalHandler);
        });

        // 方式二：会重复注册
        // 获取 Laravel 的异常处理器实例
        // $handler = app(ExceptionHandler::class);
        // 自定义的异常处理
        // app()->bind(ExceptionHandler::class, function () use ($handler) {
        //     return new ToolsParseExceptionHandler($handler);
        // });

        // 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
        WhereHasInBuilder::register($this);
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath()
    {
        $path = __DIR__.'/Commands/stubs';
        Stub::setBasePath($path);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices()
    {
        $this->app->singleton(Contracts\RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new Laravel\LaravelFileRepository($app, $path);
        });
        $this->app->singleton(Contracts\ActivatorInterface::class, function ($app) {
            return new FileActivator($app);
        });
        // 定义 app('modules');
        $this->app->alias(Contracts\RepositoryInterface::class, 'modules');

        // 定义 app('trace')
        $this->app->singleton(Handle::class, function ($app) {
            return new Handle($app);
        });
        $this->app->alias(Handle::class, 'trace');
    }
}
