<?php

namespace zxf\laravel\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use zxf\laravel\Modules\Contracts\RepositoryInterface;
use zxf\laravel\Modules\Laravel\LaravelFileRepository;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, LaravelFileRepository::class);
    }
}
