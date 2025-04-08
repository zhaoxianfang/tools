<?php

namespace zxf\Laravel\Modules\Activators;

use Illuminate\Container\Container;
use zxf\Laravel\Modules\Contracts\ActivatorInterface;
use zxf\Laravel\Modules\Module;

class FileActivator implements ActivatorInterface
{
    public function __construct(Container $app) {}

    /**
     * {@inheritDoc}
     */
    public function delete(Module $module): void {}
}
