<?php

namespace zxf\Laravel\Modules\Contracts;

use zxf\Laravel\Modules\Module;

interface ActivatorInterface
{
    /**
     * Deletes a module activation status
     */
    public function delete(Module $module): void;
}
