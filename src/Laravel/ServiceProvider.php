<?php

namespace zxf\Laravel;

use zxf\Laravel\BuilderQuery\Builder as WhereHasInBuilder;
use zxf\Laravel\Modules\Contracts;
use zxf\Laravel\Modules\Laravel;
use zxf\Laravel\Modules\Activators\FileActivator;
use zxf\Laravel\Modules\Providers\ConsoleServiceProvider;
use zxf\Laravel\Modules\Providers\ContractsServiceProvider;
use zxf\Laravel\Modules\Providers\ModulesRouteServiceProvider;
use zxf\Laravel\Modules\Providers\AutoLoadModulesProviders;
use Illuminate\Pagination\Paginator;
use zxf\Laravel\Modules\Middleware\ExtendMiddleware;

/**
 * 支持 laravel 服务注入
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function __construct($app)
    {
        is_laravel() && parent::__construct($app);
    }

    public function boot()
    {
        if (!is_laravel()) {
            return;
        }
        $this->bootPublishes();

        // 加载模块boot
        $this->mapModuleBoot();

        // 设置数据分页模板
        $this->setPaginationView();
        // 使用提示
        $this->tips();
    }

    public function register()
    {
        if (!is_laravel()) {
            return;
        }
        // 注册modules 模块服务
        $this->registerModulesServices();

        $this->registerProviders();

        $this->mergeConfigFrom(__DIR__ . '/../../config/modules.php', 'modules');

        // 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
        WhereHasInBuilder::register($this);
    }


    public function provides()
    {

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
            __DIR__ . '/../../config/' => config_path(''),
        ], 'modules');

        // 发布Modules模块文件组
        $this->publishes([
            __DIR__ . '/../../publishes/' => base_path(''),
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
        $this->app->singleton(ExtendMiddleware::class);
        $this->app->alias(ExtendMiddleware::class, 'tools_middleware');

        // 注册路由
        $this->app->register(ModulesRouteServiceProvider::class);
        // 自动加载 Providers 服务提供者
        AutoLoadModulesProviders::start($this->app);

        // 注册异常报告 [注册异常后，会替代laravel 自身的 错误机制] 不推荐
        // set_error_handler('exception_handler');
    }

    protected function mapModuleBoot()
    {
        if (!is_dir(base_path(modules_name()))) {
            return false;
        }
        $modules = array_slice(scandir(base_path(modules_name())), 2);
        foreach ($modules as $module) {
            $moduleLower = strtolower($module);
            if (is_dir(base_path(modules_name() . '/' . $module))) {
                $this->registerTranslations($module, $moduleLower);
                $this->registerConfig($module, $moduleLower);
                $this->registerViews($module, $moduleLower);
                $this->loadMigrationsFrom(module_path($module, 'Database/Migrations'));
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
        $langPath = resource_path('lang/modules/' . $moduleLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $moduleLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($module, 'Resources/lang'), $moduleLower);
            $this->loadJsonTranslationsFrom(module_path($module, 'Resources/lang'));
        }
    }

    /**
     * Register views.
     * 然后就可以使用 view('apidoc::test') 去访问Apidoc/Resources/views里面的视图文件了
     *
     * @return void
     */
    public function registerViews($module, $moduleLower)
    {
        $viewPath = resource_path('views/modules/' . $moduleLower);

        $sourcePath = module_path($module, 'Resources/views');

        if (config('modules.publishes_views', true)) {
            $this->publishes([
                $sourcePath => $viewPath,
            ], ['views', $moduleLower . '-module-views']);
        }
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module, $moduleLower), [$sourcePath]), $moduleLower);

        $this->loadViewsFrom(module_path($module, 'Resources/views'), $moduleLower);
    }

    private function getPublishableViewPaths($module, $moduleLower): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $moduleLower)) {
                $paths[] = $path . '/modules/' . $moduleLower;
            }
        }
        return $paths;
    }

    /**
     * Register config.
     *
     * @return false|void
     */
    protected function registerConfig($module, $moduleLower)
    {
        if (!is_file(module_path($module, 'Config/config.php'))) {
            return false;
        }
        if (config('modules.publishes_config', false)) {
            $this->publishes([
                module_path($module, 'Config/config.php') => config_path($moduleLower . '.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(
            module_path($module, 'Config/config.php'), $moduleLower
        );
    }

    // 使用多模块提示
    protected function tips()
    {
        if (app()->runningInConsole() && !is_dir(base_path(modules_name()))) {
            echo PHP_EOL . '==================================================================================' . PHP_EOL;
            echo '| 插    件 | composer require zxf/tools                                          |' . PHP_EOL;
            echo '| 格    言 | 人生在勤，不索何获                                                  |' . PHP_EOL;
            echo '| 模块发布 | php artisan vendor:publish --provider="zxf\Laravel\ServiceProvider" |' . PHP_EOL;
            echo '| 文档地址 | http://0l0.net/docs/2                                        |' . PHP_EOL;
            echo '==================================================================================' . PHP_EOL;
        }
    }
}
