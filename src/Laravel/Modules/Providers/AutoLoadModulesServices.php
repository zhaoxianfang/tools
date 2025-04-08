<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Contracts\Foundation\Application;
use ReflectionException;

class AutoLoadModulesServices
{
    /**
     * 开始自动加载 Modules 模块里面的服务
     *
     *
     * @throws ReflectionException
     */
    public static function handle(Application $app)
    {
        self::autoLoadProvider($app);
        self::autoLoadCommands($app);
    }

    /**
     * 自动加载 Modules 模块里面的 Providers 文件夹，并注册为 服务提供者
     *
     * @return false|void
     *
     * @throws ReflectionException
     */
    public static function autoLoadProvider(Application $app)
    {
        $modulesName = modules_name();

        if (! is_dir(base_path($modulesName))) {
            return false;
        }
        $providerPath = config('modules.paths.generator.provider.path');
        $modules = array_slice(scandir(base_path($modulesName)), 2);
        foreach ($modules as $module) {
            $moduleProvidersPath = "{$modulesName}/{$module}/{$providerPath}";
            if (is_dir($paths = base_path($moduleProvidersPath))) {
                $namespace = '';
                foreach ((new \Symfony\Component\Finder\Finder)->in($paths)->files() as $provider) {
                    $provider = $namespace.str_replace(['/', '.php'], ['\\', ''], \Illuminate\Support\Str::after($provider->getRealPath(), realpath(base_path()).DIRECTORY_SEPARATOR));
                    if (is_subclass_of($provider, \Illuminate\Support\ServiceProvider::class) && ! (new \ReflectionClass($provider))->isAbstract()) {
                        $app->register($provider);
                    }
                }
            }
        }
    }

    /**
     * 自动加载 Modules 模块里面的 Commands 一级文件夹，并注册为 命令
     *
     * @example 创建 模块下的 command : php artisan module:make-command TestCommand Test
     *
     * @return false|void
     *
     * @throws ReflectionException
     */
    public static function autoLoadCommands(Application $app)
    {
        $modulesName = modules_name();

        if (! is_dir(base_path($modulesName))) {
            return false;
        }
        $commandDir = config('modules.paths.generator.command.path');
        $modules = array_slice(scandir(base_path($modulesName)), 2);
        foreach ($modules as $module) {
            $moduleConsolePath = "{$modulesName}/{$module}/{$commandDir}";
            if (is_dir($paths = base_path($moduleConsolePath))) {
                $namespace = '';
                foreach ((new \Symfony\Component\Finder\Finder)->in($paths)->files() as $command) {
                    $command = $namespace.str_replace(['/', '.php'], ['\\', ''], \Illuminate\Support\Str::after($command->getRealPath(), realpath(base_path()).DIRECTORY_SEPARATOR));
                    if (is_subclass_of($command, \Illuminate\Console\Command::class) && ! (new \ReflectionClass($command))->isAbstract()) {
                        \Illuminate\Console\Application::starting(function ($artisan) use ($command) {
                            $artisan->resolve($command);
                        });
                    }
                }
            }
        }
    }
}
