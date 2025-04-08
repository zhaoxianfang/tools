<?php

namespace zxf\Laravel\Modules\Activators;

use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use zxf\Laravel\Modules\Contracts\ActivatorInterface;
use zxf\Laravel\Modules\Module;

class FileActivator implements ActivatorInterface
{
    /**
     * Laravel Filesystem instance
     */
    private Filesystem $files;

    /**
     * Laravel config instance
     */
    private Config $config;

    /**
     * Array of modules activation statuses
     */
    private array $modulesStatuses;

    public function __construct(Container $app)
    {
        $this->files = $app['files'];
        $this->config = $app['config'];
        $this->modulesStatuses = $this->readJson();
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->modulesStatuses = [];
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
    public function hasStatus(Module|string $module, bool $status): bool
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
        $this->modulesStatuses[$name] = $status;
        $this->writeJson();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Module $module): void
    {
        if (! isset($this->modulesStatuses[$module->getName()])) {
            return;
        }
        unset($this->modulesStatuses[$module->getName()]);
        $this->writeJson();
    }

    /**
     * Writes the activation statuses in a file, as json
     */
    private function writeJson(): void {}

    /**
     * Reads the json file that contains the activation statuses.
     *
     * @throws FileNotFoundException
     */
    private function readJson(): array
    {
        return [];
    }
}
