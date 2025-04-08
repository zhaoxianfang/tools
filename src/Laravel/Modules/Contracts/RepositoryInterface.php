<?php

namespace zxf\Laravel\Modules\Contracts;

use zxf\Laravel\Modules\Exceptions\ModuleNotFoundException;
use zxf\Laravel\Modules\Module;

interface RepositoryInterface
{
    /**
     * Get all modules.
     *
     * @return mixed
     */
    public function all();

    /**
     * Scan & get all available modules.
     *
     * @return array
     */
    public function scan();

    /**
     * Get modules as modules collection instance.
     *
     * @return \zxf\Laravel\Modules\Collection
     */
    public function toCollection();

    /**
     * Get scanned paths.
     *
     * @return array
     */
    public function getScanPaths();

    /**
     * Get list of enabled modules.
     *
     * @return mixed
     */
    public function allEnabled();

    /**
     * Get count from all modules.
     *
     * @return int
     */
    public function count();

    /**
     * Get all ordered modules.
     *
     * @param  string  $direction
     * @return mixed
     */
    public function getOrdered($direction = 'asc');

    /**
     * Get modules by the given status.
     *
     * @param  int  $status
     * @return mixed
     */
    public function getByStatus($status);

    /**
     * Find a specific module.
     *
     * @return Module|null
     */
    public function find(string $name);

    /**
     * Find a specific module. If there return that, otherwise throw exception.
     *
     *
     * @return mixed
     */
    public function findOrFail(string $name);

    public function getModulePath($moduleName);

    /**
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFiles();

    /**
     * Get a specific config data from a configuration file.
     *
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, $default = null);

    /**
     * Get a module path.
     */
    public function getPath(): string;

    /**
     * Boot the modules.
     */
    public function boot(): void;

    /**
     * Register the modules.
     */
    public function register(): void;

    /**
     * Get asset path for a specific module.
     */
    public function assetPath(string $module): string;

    /**
     * Delete a specific module.
     *
     * @throws \zxf\Laravel\Modules\Exceptions\ModuleNotFoundException
     */
    public function delete(string $module): bool;

}
