<?php

namespace zxf\laravel;

use Illuminate\Database\Eloquent\Builder;
use zxf\laravel\BuilderQuery;
use zxf\laravel\Modules\Contracts;
use zxf\laravel\Modules\Laravel;
use zxf\laravel\Modules\Activators\FileActivator;
use zxf\laravel\Modules\Providers\ConsoleServiceProvider;
use zxf\laravel\Modules\Providers\ContractsServiceProvider;

/**
 * 支持 laravel 服务注入
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->bootPublishes();
//        $this->bootConsole();
//
//        // 加载模块boot
//        $this->mapModuleBoot();
    }

    public function register()
    {
        // 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
        $this->registerBuilderQuery();

//        $this->registerProviders();
//
//        // 注册modules 模块服务
//        if (app()->runningInConsole()) {
//            $this->registerModulesServices();
//        }

        $this->mergeConfigFrom(__DIR__ . '/../../config/modules.php', 'modules');
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

    // 加载命令
    protected function bootConsole() {
        if ($this->app->runningInConsole()) {
            // 命令行
        }
    }

    // 加载发布文件
    protected function bootPublishes() {
        $this->publishes([
            __DIR__ . '/../../config/oauth.php' => config_path('oauth.php')
        ], 'zxf');

        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php')
        ], 'zxf');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/app.php', 'app'
        );
        // 发布文件组
        $this->publishes([
            __DIR__ . '/../../Modules/' => base_path()
        ], 'zxf');
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
    }

}
