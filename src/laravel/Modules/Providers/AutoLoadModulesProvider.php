<?php

namespace zxf\laravel\Modules\Providers;

use Illuminate\Support\ServiceProvider;

class AutoLoadModulesProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->customAddProviders();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function customAddProviders()
    {
        $modulesName = config('modules.namespace', 'Modules');

        if (!is_dir(base_path($modulesName))) {
            return false;
        }
        $modules = array_slice(scandir(base_path($modulesName)), 2);
        foreach ($modules as $module) {
            $moduleProvidersPath = "{$modulesName}/{$module}/Providers";
            if (is_dir($moduleProvidersPath)) {
                $paths     = base_path($moduleProvidersPath);
                $namespace = '';
                foreach ((new \Symfony\Component\Finder\Finder)->in($paths)->files() as $provider) {
                    $provider = $namespace . str_replace(['/', '.php'], ['\\', ''], \Illuminate\Support\Str::after($provider->getRealPath(), realpath(base_path()) . DIRECTORY_SEPARATOR));
                    if (is_subclass_of($provider, \Illuminate\Support\ServiceProvider::class) && !(new \ReflectionClass($provider))->isAbstract()) {
                        $this->app->register($provider);
                    }
                }
            }
        }
    }
}
