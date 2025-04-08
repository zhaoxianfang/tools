<?php

namespace zxf\Laravel\Modules;

use Countable;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use zxf\Laravel\Modules\Contracts\RepositoryInterface;
use zxf\Laravel\Modules\Exceptions\InvalidAssetPath;
use zxf\Laravel\Modules\Exceptions\ModuleNotFoundException;

abstract class FileRepository implements Countable, RepositoryInterface
{
    use Macroable;

    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The module path.
     *
     * @var string|null
     */
    protected $path;

    /**
     * The scanned paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * @var string
     */
    protected $stubPath;

    private UrlGenerator $url;

    private ConfigRepository $config;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var CacheManager
     */
    private $cache;

    private static array $modules = [];

    /**
     * The constructor.
     */
    public function __construct(Container $app, ?string $path = null)
    {
        $this->app = $app;
        $this->path = $path;
        $this->url = $app['url'];
        $this->config = $app['config'];
        $this->files = $app['files'];
        $this->cache = $app['cache'];
    }

    /**
     * Add other module location.
     *
     * @param  string  $path
     * @return $this
     */
    public function addLocation($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * Get all additional paths.
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get scanned modules paths.
     */
    public function getScanPaths(): array
    {
        return [];
    }

    /**
     * Creates a new Module instance
     *
     * @param  mixed  ...$args
     * @return Module
     */
    abstract protected function createModule(...$args);

    /**
     * Get & scan all modules.
     *
     * @return array
     */
    public function scan()
    {
        if (! empty(self::$modules) && ! $this->app->runningUnitTests()) {
            return self::$modules;
        }

        $modules = [];
        $modulesArr = array_slice(scandir(base_path(modules_name())), 2);

        foreach ($modulesArr as $module) {
            if (is_dir(base_path(modules_name().'/'.$module))) {
                $modules[$module] = $this->createModule($this->app, $module, module_path($module));
            }
        }

        return $modules;
    }

    /**
     * Get all modules.
     */
    public function all(): array
    {
        return $this->scan();
    }

    /**
     * Get all modules as collection instance.
     */
    public function toCollection(): Collection
    {
        return new Collection($this->scan());
    }

    /**
     * Get modules by status.
     */
    public function getByStatus($status): array
    {
        $modules = [];

        /** @var Module $module */
        foreach ($this->all() as $name => $module) {
            $modules[$name] = $module;
        }

        return $modules;
    }

    /**
     * Determine whether the given module exist.
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get list of enabled modules.
     */
    public function allEnabled(): array
    {
        return $this->getByStatus(true);
    }

    /**
     * Get count from all modules.
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Get all ordered modules.
     *
     * @param  string  $direction
     */
    public function getOrdered($direction = 'asc'): array
    {
        $modules = $this->allEnabled();

        uasort($modules, function (Module $a, Module $b) use ($direction) {
            if ($a->get('priority') === $b->get('priority')) {
                return 0;
            }

            if ($direction === 'desc') {
                return $a->get('priority') < $b->get('priority') ? 1 : -1;
            }

            return $a->get('priority') > $b->get('priority') ? 1 : -1;
        });

        return $modules;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path ?: $this->config('paths.modules', base_path('Modules'));
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        foreach ($this->getOrdered() as $module) {
            $module->register();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        foreach ($this->getOrdered() as $module) {
            $module->boot();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function find(string $name)
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Find a specific module, if there return that, otherwise throw exception.
     *
     *
     * @return Module
     *
     * @throws ModuleNotFoundException
     */
    public function findOrFail(string $name)
    {
        $module = $this->find($name);

        if ($module !== null) {
            return $module;
        }

        throw new ModuleNotFoundException("Module [{$name}] does not exist!");
    }

    /**
     * Get module path for a specific module.
     *
     *
     * @return string
     */
    public function getModulePath($module)
    {
        try {
            return $this->findOrFail($module)->getPath().'/';
        } catch (ModuleNotFoundException $e) {
            return $this->getPath().'/'.Str::studly($module).'/';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function assetPath(string $module): string
    {
        return $this->config('paths.assets').'/'.$module;
    }

    /**
     * {@inheritDoc}
     */
    public function config(string $key, $default = null)
    {
        return $this->config->get('modules.'.$key, $default);
    }

    /**
     * Get storage path for module used.
     */
    public function getUsedStoragePath(): string
    {
        $directory = storage_path('app/modules');
        if ($this->getFiles()->exists($directory) === false) {
            $this->getFiles()->makeDirectory($directory, 0777, true);
        }

        $path = storage_path('app/modules/modules.used');
        if (! $this->getFiles()->exists($path)) {
            $this->getFiles()->put($path, '');
        }

        return $path;
    }

    /**
     * Get module used for cli session.
     *
     * @throws \zxf\Laravel\Modules\Exceptions\ModuleNotFoundException
     */
    public function getUsedNow(): string
    {
        return $this->findOrFail($this->getFiles()->get($this->getUsedStoragePath()));
    }

    /**
     * Get laravel filesystem instance.
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * Get module assets path.
     */
    public function getAssetsPath(): string
    {
        return $this->config('paths.assets');
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $name): bool
    {
        return $this->findOrFail($name)->delete();
    }
}
