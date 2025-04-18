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
use zxf\Laravel\Modules\Constants\ModuleEvent;
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
     */
    protected ?string $path;

    /**
     * The scanned paths.
     */
    protected array $paths = [];

    /**
     * Stub path
     */
    protected ?string $stubPath = null;

    /**
     * URL Generator
     */
    private UrlGenerator $url;

    /**
     * Config Repository
     */
    private ConfigRepository $config;

    /**
     * File system
     */
    private Filesystem $files;

    /**
     * Cache Manager
     */
    private CacheManager $cache;

    private static $modules = [];

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
     */
    public function addLocation(string $path): self
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
     * 获取遍历的 modules 文件夹.
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;

        $paths[] = $this->getPath();

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Creates a new Module instance
     */
    abstract protected function createModule(Container $app, string $name, string $path): Module;

    /**
     * Get & scan all modules.
     */
    public function scan(): array
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

        self::$modules = $modules;

        return self::$modules;
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
            if ($module->isStatus($status)) {
                $modules[$name] = $module;
            }
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
     * Get list of disabled modules.
     */
    public function allDisabled(): array
    {
        return $this->getByStatus(false);
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
     */
    public function getOrdered(string $direction = 'asc'): array
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
    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Find a specific module, if there return that, otherwise throw exception.
     *
     * @throws ModuleNotFoundException
     */
    public function findOrFail(string $name): Module
    {
        $module = $this->find($name);

        if ($module !== null) {
            return $module;
        }

        throw new ModuleNotFoundException("Module [{$name}] does not exist!");
    }

    /**
     * Get all modules as laravel collection instance.
     */
    public function collections($status = 1): Collection
    {
        return new Collection($this->getByStatus($status));
    }

    /**
     * Get module path for a specific module.
     */
    public function getModulePath($module): string
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
     * Set module used for cli session.
     *
     * @throws ModuleNotFoundException
     */
    public function setUsed($name)
    {
        $module = $this->findOrFail($name);

        $this->getFiles()->put($this->getUsedStoragePath(), $module);

        $module->fireEvent(ModuleEvent::USED);
    }

    /**
     * Forget the module used for cli session.
     */
    public function forgetUsed()
    {
        if ($this->getFiles()->exists($this->getUsedStoragePath())) {
            $this->getFiles()->delete($this->getUsedStoragePath());
        }
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
     * Get asset url from a specific module.
     *
     * @throws InvalidAssetPath
     */
    public function asset(string $asset): string
    {
        if (Str::contains($asset, ':') === false) {
            throw InvalidAssetPath::missingModuleName($asset);
        }
        [$name, $url] = explode(':', $asset);

        $baseUrl = str_replace(public_path().DIRECTORY_SEPARATOR, '', $this->getAssetsPath());

        $url = $this->url->asset($baseUrl."/{$name}/".$url);

        return str_replace(['http://', 'https://'], '//', $url);
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(string $name): bool
    {
        return $this->findOrFail($name)->isEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function isDisabled(string $name): bool
    {
        return ! $this->isEnabled($name);
    }

    /**
     * Enabling a specific module.
     *
     * @throws \zxf\Laravel\Modules\Exceptions\ModuleNotFoundException
     */
    public function enable(string $name)
    {
        $this->findOrFail($name)->enable();
    }

    /**
     * Disabling a specific module.
     *
     * @throws \zxf\Laravel\Modules\Exceptions\ModuleNotFoundException
     */
    public function disable(string $name)
    {
        $this->findOrFail($name)->disable();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $name): bool
    {
        return $this->findOrFail($name)->delete();
    }

    /**
     * Get stub path.
     */
    public function getStubPath(): ?string
    {
        if ($this->stubPath !== null) {
            return $this->stubPath;
        }

        return $this->stubPath;
    }

    /**
     * Set stub path.
     */
    public function setStubPath(string $stubPath): self
    {
        $this->stubPath = $stubPath;

        return $this;
    }

    public function resetModules(): static
    {
        self::$modules = [];

        return $this;
    }
}
