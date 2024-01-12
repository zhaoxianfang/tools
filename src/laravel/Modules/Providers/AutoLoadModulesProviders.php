<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Contracts\Foundation\Application;
use ReflectionException;

class AutoLoadModulesProviders
{

    /**
     * 开始自动加载 Modules 模块里面的 Providers 文件夹，并注册为 服务提供者
     *
     * @param Application $app
     *
     * @return false|void
     * @throws ReflectionException
     */
    public static function start(Application $app)
    {
        $modulesName = modules_name();

        if (!is_dir(base_path($modulesName))) {
            return false;
        }
        $modules = array_slice(scandir(base_path($modulesName)), 2);
        foreach ($modules as $module) {
            $moduleProvidersPath = "{$modulesName}/{$module}/Providers";
            if (is_dir($paths = base_path($moduleProvidersPath))) {
                $namespace = '';
                foreach ((new \Symfony\Component\Finder\Finder)->in($paths)->files() as $provider) {
                    $provider = $namespace . str_replace(['/', '.php'], ['\\', ''], \Illuminate\Support\Str::after($provider->getRealPath(), realpath(base_path()) . DIRECTORY_SEPARATOR));
                    if (is_subclass_of($provider, \Illuminate\Support\ServiceProvider::class) && !(new \ReflectionClass($provider))->isAbstract()) {
                        $app->register($provider);
                    }
                }
            }
        }
    }
}
