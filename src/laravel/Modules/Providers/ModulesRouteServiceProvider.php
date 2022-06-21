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

    protected function getModulesName()
    {
        return config('modules.namespace', 'Modules');
    }

    protected function mapModuleRoutes()
    {
        if (!is_dir(base_path($this->getModulesName()))) {
            return false;
        }
        $modules = array_slice(scandir(base_path($this->getModulesName())), 2);
        foreach ($modules as $module) {
            $this->mapModuleRoute($module);
        }
    }

    /**
     * 查询路由文件夹下的路由文件，并根据该文件名设置路由命名和中间件
     *    例如：Routes 文件夹下有一个 api.php 文件，则该路由文件对应的控制器路径为 \Http\Controllers\Api\ ,使用的中间件为 api
     *
     * @param $module
     * @return void
     */
    protected function mapModuleRoute($module)
    {
        $pathDir    = base_path($this->getModulesName() . "/{$module}/Routes/");
        $routeFiles = $this->findRouteFile($pathDir);
        foreach ($routeFiles as $routeName) {
            $path              = $pathDir . $routeName . '.php';
            $lowRouteName      = strtolower($routeName);
            // 默认使用web中间件
            $useMiddlewareName = in_array($lowRouteName, ['api', 'web']) ? $lowRouteName : 'web';
            Route::namespace($this->getModulesName() . "\\{$module}\Http\Controllers\\" . ucfirst($lowRouteName))
                // ->prefix(underline_convert($module)) // ->prefix('admin') // 是否设置统一的路由前缀
                ->prefix('') // 根据实际的业务逻辑去路由文件中自定义前缀和路由名等
                ->middleware([$useMiddlewareName])
                ->group($path);
        }
    }

    // 查找路由文件[去除后缀]
    protected function findRouteFile($dir = '')
    {
        $filesList = [];
        if (!is_dir($dir)) {
            return $filesList;
        }
        //首先先读取文件夹
        $files = scandir($dir);
        //遍历文件夹
        foreach ($files as $route) {
            $ext = pathinfo($route, PATHINFO_EXTENSION);
            if ($ext == 'php') {
                $filesList[] = pathinfo($route, PATHINFO_FILENAME);
            }
        }
        return $filesList;
    }
}
