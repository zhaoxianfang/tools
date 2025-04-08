<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class ModulesRouteServiceProvider extends RouteServiceProvider
{
    /**
     * 扩展 boot 功能
     */
    public function boot(): void
    {
        parent::boot();

    }

    public function map(): void
    {
        // 加载自定义模块路由
        if (! $this->app->routesAreCached()) {
            $this->mapModuleRoutes();
        }
    }

    protected function getModulesName()
    {
        return modules_name();
    }

    protected function mapModuleRoutes()
    {
        if (! is_dir(base_path($this->getModulesName()))) {
            return false;
        }
        $modules = scandir(base_path($this->getModulesName()));
        foreach ($modules as $module) {
            if (str_starts_with($module, '.')) {
                continue;
            }
            $this->mapModuleRoute($module);
        }
    }

    /**
     * 查询路由文件夹下的路由文件，并根据该文件名设置路由命名和中间件
     *    例如：Routes 文件夹下有一个 api.php 文件，则该路由文件对应的控制器路径为 \Http\Controllers\Api\ ,使用的中间件为 api
     *
     *
     * @return void
     */
    protected function mapModuleRoute(string $module)
    {
        // 允许自动加载的中间件组
        $userMiddlewareGroups = config('modules.allow_automatic_load_middleware_groups', []);
        // 是否自动使用路由文件同名中间件组
        $autoUseMiddleware = config('modules.auto_use_middleware_groups', true);
        // 需要自动添加上同名 `xxx`前缀和 `xxx.` 路由命名 的路由文件
        $routeNeedAddPrefixAndName = config('modules.route_need_add_prefix_and_name', ['api']);

        $routePath = config('modules.paths.generator.rules.path');

        $pathDir = base_path($this->getModulesName()."/{$module}/{$routePath}/");
        $routeFiles = $this->findRouteFile($pathDir);
        foreach ($routeFiles as $routeName) {
            $path = $pathDir.$routeName.'.php';
            $lowRouteName = underline_convert($routeName);

            // 判断中间件是否存在 中间件组中`$middlewareGroups`
            $useMiddlewareName = in_array($lowRouteName, $userMiddlewareGroups) ? $lowRouteName : '';

            // 中间件
            $middlewareGroup = (! $autoUseMiddleware || empty($useMiddlewareName)) ? [] : [$useMiddlewareName];
            // 需要自动加上路由前缀的文件，例如 api.php
            $addPrefix = (! empty($routeNeedAddPrefixAndName) && in_array($lowRouteName, $routeNeedAddPrefixAndName)) ? $lowRouteName : '';
            // 需要自动加上路由名称的文件，例如 api.php
            $addName = ! empty($addPrefix) ? ($lowRouteName.'.') : '';

            Route::namespace($this->getModulesName()."\\{$module}\Http\Controllers\\".ucfirst($lowRouteName))
                ->prefix($addPrefix) // ->prefix('admin') // 是否设置统一的路由前缀
                ->name($addName) //
                ->middleware($middlewareGroup)
                ->group($path);
        }
    }

    // 查找路由文件[去除后缀]
    protected function findRouteFile($dir = '')
    {
        $filesList = [];
        if (! is_dir($dir)) {
            return $filesList;
        }
        // 首先先读取文件夹
        $files = scandir($dir);
        // 遍历文件夹
        foreach ($files as $route) {
            $ext = pathinfo($route, PATHINFO_EXTENSION);
            if ($ext == 'php') {
                $filesList[] = pathinfo($route, PATHINFO_FILENAME);
            }
        }

        return $filesList;
    }
}
