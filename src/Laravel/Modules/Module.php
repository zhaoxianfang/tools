<?php

namespace zxf\Laravel\Modules;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Translation\Translator;
use zxf\Laravel\Modules\Constants\ModuleEvent;
use zxf\Laravel\Modules\Contracts\ActivatorInterface;
use zxf\Laravel\Modules\Json;

abstract class Module
{
    use Macroable;

    /**
     * The laravel|lumen application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The module name.
     */
    protected $name;

    /**
     * The module path.
     *
     * @var string
     */
    protected $path;

    /**
     * @var array of cached Json objects, keyed by filename
     */
    protected $moduleJson = [];

    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ActivatorInterface
     */
    private $activator;

    /**
     * The constructor.
     */
    public function __construct(Container $app, string $name, $path)
    {
        $this->name = $name;
        $this->path = $path;
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->translator = $app['translator'];
        $this->activator = $app[ActivatorInterface::class];
        $this->app = $app;
    }

    /**
     * Returns an array of assets
     */
    public static function getAssets(): array
    {
        return [];
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get name in lower case.
     */
    public function getLowerName(): string
    {
        return strtolower($this->name);
    }

    /**
     * Get name in studly case.
     */
    public function getStudlyName(): string
    {
        return Str::studly($this->name);
    }

    /**
     * Get name in snake case.
     */
    public function getSnakeName(): string
    {
        return Str::snake($this->name);
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->get('description');
    }

    /**
     * Get priority.
     */
    public function getPriority(): string
    {
        return $this->get('priority');
    }

    /**
     * Get path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get app path.
     */
    public function getAppPath(): string
    {
        $app_path = rtrim($this->getExtraPath(config('modules.paths.app_folder', '')), '/');

        return is_dir($app_path) ? $app_path : $this->getPath();
    }

    /**
     * Set path.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath($path): Module
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        if (config('modules.register.translations', true) === true) {
            $this->registerTranslation();
        }

        if ($this->isLoadFilesOnBoot()) {
            $this->registerFiles();
        }

        $this->fireEvent(ModuleEvent::BOOT);
    }

    /**
     * Register module's translation.
     */
    protected function registerTranslation(): void
    {
        $lowerName = $this->getLowerName();

        $langPath = $this->getPath().'/Resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $lowerName);
        }
    }

    /**
     * Get json contents from the cache, setting as needed.
     *
     * @param  string  $file
     */
    public function json($file = null): Collection
    {
        return Collection::make([]);
    }

    /**
     * Get a specific data from json file by given the key.
     *
     * @param  null  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->json()->get($key, $default);
    }

    /**
     * Get a specific data from composer.json file by given the key.
     *
     * @param  null  $default
     * @return mixed
     */
    public function getComposerAttr($key, $default = null)
    {
        return $this->json('composer.json')->get($key, $default);
    }

    /**
     * Register the module.
     */
    public function register(): void
    {
        $this->registerAliases();

        $this->registerProviders();

        if ($this->isLoadFilesOnBoot() === false) {
            $this->registerFiles();
        }

        $this->fireEvent(ModuleEvent::REGISTER);
    }

    /**
     * fire the module event.
     */
    public function fireEvent(string $event): void
    {
        $this->app['events']->dispatch(sprintf('modules.%s.%s', $this->getLowerName(), $event), [$this]);
    }

    /**
     * Register the aliases from this module.
     */
    abstract public function registerAliases(): void;

    /**
     * Register the service providers from this module.
     */
    abstract public function registerProviders(): void;

    /**
     * Get the path to the cached *_module.php file.
     */
    abstract public function getCachedServicesPath(): string;

    /**
     * Register the files from this module.
     */
    protected function registerFiles(): void
    {
        foreach ($this->get('files', []) as $file) {
            include $this->path.'/'.$file;
        }
    }

    /**
     * Handle call __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getStudlyName();
    }

    /**
     * Determine whether the given status same with the current module status.
     */
    public function isStatus(bool $status): bool
    {
        return $this->activator->hasStatus($this, $status);
    }

    /**
     * Determine whether the current module activated.
     */
    public function isEnabled(): bool
    {
        // return $this->activator->hasStatus($this, true);
        return true;
    }

    /**
     *  Determine whether the current module not disabled.
     */
    public function isDisabled(): bool
    {
        return ! $this->isEnabled();
    }

    /**
     * Set active state for current module.
     */
    public function setActive(bool $active): void
    {
        $this->activator->setActive($this, $active);
    }

    /**
     * Disable the current module.
     */
    public function disable(): void
    {
        $this->fireEvent(ModuleEvent::DISABLING);

        $this->activator->disable($this);
        $this->flushCache();

        $this->fireEvent(ModuleEvent::DISABLED);
    }

    /**
     * Enable the current module.
     */
    public function enable(): void
    {
        $this->fireEvent(ModuleEvent::ENABLING);

        $this->activator->enable($this);
        $this->flushCache();

        $this->fireEvent(ModuleEvent::ENABLED);
    }

    /**
     * Delete the current module.
     */
    public function delete(): bool
    {
        $this->fireEvent(ModuleEvent::DELETING);

        $this->activator->delete($this);

        $result = $this->json()->getFilesystem()->deleteDirectory($this->getPath());

        $this->fireEvent(ModuleEvent::DELETED);

        return $result;
    }

    /**
     * Get extra path.
     */
    public function getExtraPath(string $path): string
    {
        return $this->getPath().'/'.$path;
    }

    /**
     * Check if can load files of module on boot method.
     */
    protected function isLoadFilesOnBoot(): bool
    {
        return config('modules.register.files', 'register') === 'boot' &&
            // force register method if option == boot && app is AsgardCms
            ! class_exists('\Modules\Core\Foundation\AsgardCms');
    }

    private function flushCache(): void
    {
        if (config('modules.cache.enabled')) {
            $this->cache->store(config('modules.cache.driver'))->flush();
        }
    }

    /**
     * Register a translation file namespace.
     */
    private function loadTranslationsFrom(string $path, string $namespace): void
    {
        $this->translator->addNamespace($namespace, $path);
    }
}
