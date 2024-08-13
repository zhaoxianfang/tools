<?php

namespace zxf\Laravel\Modules\Activators;

use Illuminate\Container\Container;
use zxf\Laravel\Modules\Contracts\ActivatorInterface;
use zxf\Laravel\Modules\Module;

class FileActivator implements ActivatorInterface
{

    public function __construct(Container $app)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function enable(Module $module): void
    {
        $this->setActiveByName($module->getName(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function disable(Module $module): void
    {
        $this->setActiveByName($module->getName(), false);
    }

    /**
     * {@inheritDoc}
     */
    public function hasStatus(Module $module, bool $status): bool
    {
        return $status;
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active);
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveByName(string $name, bool $status): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Module $module): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
    }
}
