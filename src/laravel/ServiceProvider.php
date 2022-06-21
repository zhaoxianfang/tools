<?php

namespace zxf\laravel;

use Illuminate\Database\Eloquent\Builder;
use zxf\laravel\BuilderQuery;
use zxf\laravel\Modules\Contracts;
use zxf\laravel\Modules\Laravel;
use zxf\laravel\Modules\Activators\FileActivator;
use zxf\laravel\Modules\Providers\ConsoleServiceProvider;
use zxf\laravel\Modules\Providers\ContractsServiceProvider;
use zxf\laravel\Modules\Providers\ModulesRouteServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;

/**
 * 支持 laravel 服务注入
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {

        // 注册modules 模块服务
        $this->registerModulesServices();

        $this->registerProviders();


        $this->mergeConfigFrom(__DIR__ . '/../../config/modules.php', 'modules');

        // 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
        $this->registerBuilderQuery();
    }

    public function boot()
    {
        $this->bootPublishes();
        // 加载模块boot
         $this->mapModuleBoot();

        // 设置数据分页模板
        $this->setPaginationView();
        // 执行命令
        $this->runCommon();
    }

    public function provides()
    {

    }

    // 框架自带的 whereHas 模型查询方法会进行全表扫描，倒是查询巨慢，使用下面几个方法进行替代实现
    protected function registerBuilderQuery()
    {
        Builder::macro('whereHasIn', function ($relationName, $callable = null) {
            return (new BuilderQuery\WhereHasIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });
        Builder::macro('orWhereHasIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasIn($relationName, $callable);
            });
        });

        Builder::macro('whereHasNotIn', function ($relationName, $callable = null) {
            return (new BuilderQuery\WhereHasNotIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasNotIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });
        Builder::macro('orWhereHasNotIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasNotIn($relationName, $callable);
            });
        });
    }

    // 设置数据分页模板
    protected function setPaginationView()
    {
        // php artisan vendor:publish --tag=laravel-pagination
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
    }

    // 加载发布文件
    protected function bootPublishes() {
        $this->publishes([
            __DIR__ . '/../../config/oauth.php' => config_path('oauth.php')
        ], 'modules');

        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php')
        ], 'modules');


        // 发布Modules模块文件组
        $this->publishes([
            __DIR__ . '/../../publishes/' => base_path('')
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
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(ContractsServiceProvider::class);
        // 注册路由
        $this->app->register(ModulesRouteServiceProvider::class);
    }

    protected function mapModuleBoot()
    {
        if(!is_dir(base_path(config('modules.namespace','Modules')))){
            return false;
        }
        $modules = array_slice(scandir(base_path(config('modules.namespace','Modules'))), 2);
        foreach ($modules as $module) {
            $moduleLower = strtolower($module);
            if (is_dir(base_path(config('modules.namespace','Modules').'/' . $module))) {
                $this->registerTranslations($module, $moduleLower);
                $this->registerConfig($module, $moduleLower);
                $this->registerViews($module, $moduleLower);
                $this->loadMigrationsFrom(module_path($module, 'Database/Migrations'));
                // $this->loadFactoriesFrom(module_path($module, 'Database/factories'));
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
            $this->loadJsonTranslationsFrom($langPath, $moduleLower);
        } else {
            $this->loadTranslationsFrom(module_path($module, 'Resources/lang'), $moduleLower);
            $this->loadJsonTranslationsFrom(module_path($module, 'Resources/lang'), $moduleLower);
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

        $this->loadViewsFrom(module_path($module, 'Resources/views'), $moduleLower);

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $moduleLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module, $moduleLower), [$sourcePath]), $moduleLower);
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
        $this->publishes([
            module_path($module, 'Config/config.php') => config_path($moduleLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($module, 'Config/config.php'), $moduleLower
        );
    }

    protected function runCommon(){
        echo 'php artisan vendor:publish --provider="zxf\laravel\ServiceProvider"' . PHP_EOL;
    }
}
