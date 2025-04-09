<?php

namespace zxf\Laravel\Modules;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use zxf\Laravel\Modules\Providers\AutoLoadModulesServices;
use zxf\Laravel\Modules\Providers\ConsoleServiceProvider;
use zxf\Laravel\Modules\Providers\ContractsServiceProvider;
use zxf\Laravel\Modules\Providers\ModulesRouteServiceProvider;
use zxf\TnCode\Providers\TnCodeValidationProviders;

abstract class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot() {}

    /**
     * Register all modules.
     */
    public function register() {}

    /**
     * Register all modules.
     */
    protected function registerModules()
    {
        if (! is_dir(base_path(modules_name()))) {
            return false;
        }
        $migrationsPath = config('modules.paths.generator.migration.path');
        $modules = array_slice(scandir(base_path(modules_name())), 2);
        foreach ($modules as $module) {
            $moduleLower = strtolower($module);
            if (is_dir(base_path(modules_name().'/'.$module))) {
                $this->registerTranslations($module, $moduleLower);
                $this->registerConfig($module, $moduleLower);
                $this->registerViews($module, $moduleLower);
                if (is_dir(module_path($module, $migrationsPath))) {
                    $this->loadMigrationsFrom(module_path($module, $migrationsPath));
                }
            }
        }
    }

    /**
     * Register package's namespaces.
     */
    protected function registerNamespaces()
    {
        // 把config 文件夹类的配置文件 发布到 config 文件夹下
        $this->publishes([
            __DIR__.'/../../../config/' => config_path(''),
        ], 'modules');

        // 发布Modules模块文件组
        $this->publishes([
            __DIR__.'/../../../publishes/' => base_path(''),
        ], 'modules');
    }

    /**
     * Register the service provider.
     */
    abstract protected function registerServices();

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Contracts\RepositoryInterface::class, 'modules'];
    }

    /**
     * 注册语言 translations.
     *
     * @return void
     */
    public function registerTranslations($module, $moduleLower)
    {
        // $langPath = resource_path('lang/modules/'.$moduleLower);
        $langPath = resource_path('lang/'.$moduleLower);
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $moduleLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $langPath = config('modules.paths.generator.lang.path');
            if (is_dir(module_path($module, $langPath))) {
                $this->loadTranslationsFrom(module_path($module, $langPath), $moduleLower);
                $this->loadJsonTranslationsFrom(module_path($module, $langPath));
            }
        }
    }

    /**
     * 注册 config.
     *
     * @return void
     */
    protected function registerConfig($module, $moduleLower)
    {
        $configPath = config('modules.paths.generator.config.path');
        if (is_dir(module_path($module, $configPath))) {
            $configs = array_slice(scandir(module_path($module, $configPath)), 2); // 2:表示从数组的第[2]项取，即不包含 . 和 ..
            foreach ($configs as $file) {
                // 获取完整文件路径
                $fullPath = module_path($module, $configPath.'/'.$file);
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
                        module_path($module, $configPath.'/'.$filename.'.php'), $moduleLower.($filename == 'config' ? '' : $configDelimiter.$filename)
                    );
                }
            }
        }
    }

    /**
     * 注册 views.
     * 然后就可以使用 view('demo::test') 去访问 Demo/Resources/views里面的视图文件了
     *
     * @return void
     */
    public function registerViews($module, $moduleLower)
    {
        $viewPath = resource_path('views/modules/'.$moduleLower);
        $viewDir = config('modules.paths.generator.views.path');
        $sourcePath = module_path($module, $viewDir);
        if (! is_dir($sourcePath)) {
            return;
        }
        if (config('modules.publishes_views', true)) {
            $this->publishes([
                $sourcePath => $viewPath,
            ], ['views', $moduleLower.'-module-views']);
        }
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module, $moduleLower), [$sourcePath]), $moduleLower);

        $this->loadViewsFrom(module_path($module, $viewDir), $moduleLower);
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
     * Register providers.
     */
    protected function registerProviders()
    {
        if (is_dir(base_path(modules_name()))) {
            $this->app->register(ConsoleServiceProvider::class);
        }
        $this->app->register(ContractsServiceProvider::class);

        // 注册路由
        $this->app->register(ModulesRouteServiceProvider::class);
        // 自动加载 多模块 下的服务
        AutoLoadModulesServices::handle($this->app);

        // 自动加载TnCode 验证器
        $this->app->register(TnCodeValidationProviders::class);

        // 注册异常报告 [注册异常后，会替代laravel 自身的 错误机制] 不推荐
        // set_error_handler('exception_handler');
    }

    /**
     * 注册中间件 并全局启用
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app->make(Kernel::class);
        // $kernel->pushMiddleware($middleware); // 追加在后面
        $kernel->prependMiddleware($middleware); // 放在最前面

        // 把中间件添加到web组
        // $kernel->appendMiddlewareToGroup('web', $middleware); // 追加在后面
        // $kernel->prependMiddlewareToGroup('web', $middleware);   // 放在最前面
    }

    // 设置数据分页模板
    protected function setPaginationView()
    {
        // php artisan vendor:publish --tag=laravel-pagination
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
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
