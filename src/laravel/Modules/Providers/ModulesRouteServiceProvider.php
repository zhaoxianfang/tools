<?php

namespace zxf\laravel\Modules\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class ModulesRouteServiceProvider extends RouteServiceProvider
{
    protected static $instance;

    /**
     * 初始化
     */
    public static function instance($app)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($app);
        }
        return self::$instance;
    }

    /**
     * 扩展 boot 功能
     *
     * @return void
     */
    public function boot()
    {
        // 加载自定义模块路由
        if (!$this->app->routesAreCached()) {
            $this->mapModuleRoutes();
        }
    }
    protected function getModulesName(){
        return config('modules.namespace','Modules');
    }

    protected function mapModuleRoutes()
    {
        if(!is_dir(base_path($this->getModulesName()))){
            return false;
        }
        $modules = array_slice(scandir(base_path($this->getModulesName())), 2);
        foreach ($modules as $module) {
            $this->mapModuleRoute($module);
        }
    }

    protected function mapModuleRoute($module)
    {
        $this->mapAdminRoutes($module);
        $this->mapApiRoutes($module);
        $this->mapWebRoutes($module);
    }

    protected function mapAdminRoutes($module)
    {
        $path = base_path($this->getModulesName()."/{$module}/Routes/admin.php");
        if (file_exists($path)) {
            Route::namespace($this->getModulesName()."\\{$module}\Http\Controllers\Admin")
                // ->prefix(underline_convert($module)) // ->prefix('admin') // 是否设置统一的路由前缀
                ->prefix('') // 根据实际的业务逻辑去路由文件中自定义前缀和路由名等
                ->middleware([
                    'admin'
                ])
                ->group($path);
        }
    }

    protected function mapApiRoutes($module)
    {
        $path = base_path($this->getModulesName()."/{$module}/Routes/api.php");
        if (file_exists($path)) {
            Route::namespace($this->getModulesName()."\\{$module}\Http\Controllers\Api")
                ->prefix('api')
                ->middleware([
                    'api',
                    \Modules\Core\Middleware\JwtAuthPrivilege::class
                ])
                ->group($path);
        }
    }

    protected function mapWebRoutes($module)
    {
        $path = base_path($this->getModulesName()."/{$module}/Routes/web.php");
        if (file_exists($path)) {
            Route::namespace($this->getModulesName()."\\{$module}\Http\Controllers\Web")
                // ->prefix(underline_convert($module)) // ->prefix('web') // 是否设置统一的路由前缀
                ->prefix('') // 根据实际的业务逻辑去路由文件中自定义前缀和路由名等
               ->middleware([
                   'web',
               ])
                ->group($path);
        }
    }
}
