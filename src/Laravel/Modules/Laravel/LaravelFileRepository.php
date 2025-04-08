<?php

namespace zxf\Laravel\Modules\Laravel;

use Illuminate\Container\Container;
use zxf\Laravel\Modules\FileRepository;

class LaravelFileRepository extends FileRepository
{
    /**
     * {@inheritdoc}
     */
    protected function createModule(Container $app, string $name, string $path): Module
    {
        return new Module($app, $name, $path);
    }
}
