<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use zxf\Laravel\Modules\Contracts\RepositoryInterface;
use zxf\Laravel\Modules\Laravel\LaravelFileRepository;

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
