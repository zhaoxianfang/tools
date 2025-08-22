# 迁移笔记

> 项目来源：https://github.com/nWidart/laravel-modules

## 前置

1、替换命名空间

```
把
Nwidart\Modules
全部替换为
zxf\Laravel\Modules
```

2、助手函数中新增 `laravel` 主版本判断相关函数

3、删除 `LumenModulesServiceProvider`

4、复制 `Middleware` 文件夹、`Providers`文件夹下的 `AutoLoadModulesServices`,`ModulesRouteServiceProvider`

## boot 方法改造

1、在LaravelModulesServiceProvider 的 boot 方法中新增

```
if (! is_laravel()) {
    return;
}
// 初始化 本地文件 session
i_session();
```

修改 registerNamespaces 方法为

```
protected function registerNamespaces()
{
    // 把config 文件夹类的配置文件 发布到 config 文件夹下
    $this->publishes([
        __DIR__.'/../../../config/' => config_path(''),
    ], 'modules');

    // 发布Modules模块文件组
    $this->publishes([
        __DIR__.'/../../../publishes/' => base_path(''),
    ], 'modules');
}
```

并删除 `scripts` 文件夹
然后把 `config.php` 重命名为 `modules.php`

2、修改 `config/modules.php` 配置

```
`generator` 里面的 `app/` 前缀都去掉
`replacements.scaffold/config` 里面添加  `, 'LOWER_NAME'`
`generator.lang.path` 设置为  `resources/lang`
`stubs.files` 里面新增
    // 自定义本地化
    'lang/en/messages' => 'Resources/lang/en/messages.php',
    'lang/en/validation' => 'Resources/lang/en/validation.php',
    'lang/zh_CN/messages' => 'Resources/lang/zh_CN/messages.php',
    'lang/zh_CN/validation' => 'Resources/lang/zh_CN/validation.php',
    'lang/zh_CN' => 'Resources/lang/zh_CN.json',
删除 stubs.files 里面的 `assets/js/app` 和 `assets/sass/app`
删除所有composer的配置项
删除:package、json、scan、activators、register 配置
// TODO
```

3、复制 `Middleware` 文件夹
并在`boot` 中添加

```php
$this->registerMiddleware(ToolsMiddleware::class);
```

并在`ModulesServiceProvider`文件中添加

```php
/**
 * 注册中间件
 *
 * @param  string  $middleware
 */
protected function registerMiddleware($middleware)
{
    $this->app['router']->aliasMiddleware('exception.handler', $middleware);

    /** @var \Illuminate\Foundation\Http\Kernel $kernel */
    $kernel = $this->app[Kernel::class];
    $kernel->pushMiddleware($middleware); // 追加
    // $kernel->prependMiddleware($middleware); // 放在最前面
    // if (isset($kernel->getMiddlewareGroups()['web'])) {
    //     $kernel->appendMiddlewareToGroup('web', $middleware); // 追加
    //     // $kernel->prependMiddlewareToGroup('web', $middleware);   // 放在最前面
    // }
}
```

4、废弃`ModuleManifest`
修改boot 里的 `registerModules` 函数

```php
protected function registerModules()
{
    if (! is_dir(base_path(modules_name()))) {
        return false;
    }
    $migrationsPath = config('modules.paths.generator.migration.path');
    $modules = array_slice(scandir(base_path(modules_name())), 2);
    foreach ($modules as $module) {
        $moduleLower = strtolower($module);
        if (is_dir(base_path(modules_name().'/'.$module))) {
            $this->registerTranslations($module, $moduleLower);
            $this->registerConfig($module, $moduleLower);
            $this->registerViews($module, $moduleLower);
            if (is_dir(module_path($module, $migrationsPath))) {
                $this->loadMigrationsFrom(module_path($module, $migrationsPath));
            }
        }
    }
}
```

删除 `registerTranslations`和`registerMigrations` 并在 `ModulesServiceProvider` 里面添加以下方法

```php
/**
 * 注册语言 translations.
 *
 * @return void
 */
public function registerTranslations($module, $moduleLower)
{
    // $langPath = resource_path('lang/modules/'.$moduleLower);
    $langPath = resource_path('lang/'.$moduleLower);
    if (is_dir($langPath)) {
        $this->loadTranslationsFrom($langPath, $moduleLower);
        $this->loadJsonTranslationsFrom($langPath);
    } else {
        $langPath = config('modules.paths.generator.lang.path');
        if (is_dir(module_path($module, $langPath))) {
            $this->loadTranslationsFrom(module_path($module, $langPath), $moduleLower);
            $this->loadJsonTranslationsFrom(module_path($module, $langPath));
        }
    }
}


/**
 * 注册 config.
 *
 * @return void
 */
protected function registerConfig($module, $moduleLower)
{
    $configPath = config('modules.paths.generator.config.path');
    if (is_dir(module_path($module, $configPath))) {
        $configs = array_slice(scandir(module_path($module, $configPath)), 2); // 2:表示从数组的第[2]项取，即不包含 . 和 ..
        foreach ($configs as $file) {
            // 获取完整文件路径
            $fullPath = module_path($module, $configPath.'/'.$file);
            if (is_file($fullPath) && str_ends_with($fullPath, '.php')) {
                $filename = pathinfo($fullPath, PATHINFO_FILENAME);
                if (config('modules.publishes_config', false)) {
                    // config.php 文件 发布成 $moduleLower.php ,其他文件 发布成 $moduleLower/$filename.php
                    $this->publishes([
                        $fullPath => config_path($moduleLower.($filename == 'config' ? '' : '/'.$filename).'.php'),
                    ], 'config');
                }
                // 读取配置文件的分隔符(config.php 文件直接使用模块名小写,针对其他文件生效)
                $configDelimiter = config('modules.multi_config_delimiter', '_');
                $this->mergeConfigFrom(
                    module_path($module, $configPath.'/'.$filename.'.php'), $moduleLower.($filename == 'config' ? '' : $configDelimiter.$filename)
                );
            }
        }
    }
}


/**
 * 注册 views.
 * 然后就可以使用 view('demo::test') 去访问 Demo/Resources/views里面的视图文件了
 *
 * @return void
 */
public function registerViews($module, $moduleLower)
{
    $viewPath = resource_path('views/modules/'.$moduleLower);
    $viewDir = config('modules.paths.generator.views.path');
    $sourcePath = module_path($module, $viewDir);
    if (! is_dir($sourcePath)) {
        return;
    }
    if (config('modules.publishes_views', true)) {
        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $moduleLower.'-module-views']);
    }
    $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module, $moduleLower), [$sourcePath]), $moduleLower);

    $this->loadViewsFrom(module_path($module, $viewDir), $moduleLower);
}
```

删除上面改写时废弃的配置 `modules.php` 里的整个`auto-discover` 配置

然后 删除`ModuleManifest`和所有引用`ModuleManifest` 的地方

5、把 `zxf-tools` 添加到 `about` 命令中

```php
// 把 zxf-tools 添加到 about 命令中
AboutCommand::add('zxf-tools', [
    'Version' => fn () => InstalledVersions::getPrettyVersion('zxf/tools'),
    'Docs' => fn () => 'https://weisifang.com/docs/2',
]);
```

6、自定义 `boot` 里面的其他自定义项

```php
// 加载debug路由
$this->loadRoutesFrom(__DIR__.'/Trace/routes/debugger.php');
// 加载tncode 路由
$this->loadRoutesFrom(__DIR__.'/../TnCode/routes.php');

// 处理 Laravel 异常
// 方式一：单次注册
$this->app->singleton(ExceptionHandler::class, function ($app) {
    // 获取原始处理器
    $originalHandler = $app->make(\Illuminate\Foundation\Exceptions\Handler::class);

    return new ToolsExceptionHandler($originalHandler);
});

// 方式二：会重复注册
// 获取 Laravel 的异常处理器实例
// $handler = app(ExceptionHandler::class);
// 自定义的异常处理
// app()->bind(ExceptionHandler::class, function () use ($handler) {
//     return new ToolsExceptionHandler($handler);
// });

// 设置数据分页模板
$this->setPaginationView();
// 使用提示
$this->tips();
```

7、 删除`getCachedModulePath`

## register 方法改造

1、合并配置文件

```php
$this->mergeConfigFrom(__DIR__.'/../../config/modules.php', 'modules');
```

2、把`registerServices`方法里面的

```php
$this->app->singleton(Contracts\ActivatorInterface::class, function ($app) {
    $activator = $app['config']->get('modules.activator');
    $class = $app['config']->get('modules.activators.'.$activator)['class'];

    if ($class === null) {
        throw InvalidActivatorClass::missingConfig();
    }

    return new $class($app);
});
```

改为

```php
$this->app->singleton(Contracts\ActivatorInterface::class, function ($app) {
    return new FileActivator($app);
});
```

并在该方法内追加

```php
// 定义 app('trace')
$this->app->singleton(Handle::class, function ($app) {
    return new Handle($app);
});
$this->app->alias(Handle::class, 'trace');
```

然后删除 `modules.php` 里面的 `activator` 配置项, `activators.file.class` 配置项

3、改造 `setupStubPath` 方法 为

```php
public function setupStubPath()
{
    $path = __DIR__.'/Commands/stubs';
    Stub::setBasePath($path);
}
```

删除 `FileRepository.php` 的 `getStubPath`方法的

```php
if ($this->config('stubs.enabled') === true) {
    return $this->config('stubs.path');
}
```

删除 `modules.php` 里面的 `stubs` 配置项的`enabled`和 `path`

4、 改造`registerProviders`为

```php
if (is_dir(base_path(modules_name()))) {
    $this->app->register(ConsoleServiceProvider::class);
}
$this->app->register(ContractsServiceProvider::class);

// 注册路由
$this->app->register(ModulesRouteServiceProvider::class);
// 自动加载 多模块 下的服务
AutoLoadModulesServices::handle($this->app);

// 自动加载TnCode 验证器
$this->app->register(TnCodeValidationProviders::class);

// 注册异常报告 [注册异常后，会替代laravel 自身的 错误机制] 不推荐
// set_error_handler('exception_handler');
```

5、删除 `modules.php` 里面的 `commands` 配置项; 删除下列命令类和引用到的地方

```
DisableCommand.php
DumpCommand.php
EnableCommand.php
InstallCommand.php
ModelPruneCommand.php
UnUseCommand.php
UpdateCommand.php
UseCommand.php
ComposerUpdateCommand.php
LaravelModulesV6Migrator.php
HelperMakeCommand.php
SetupCommand.php
UpdatePhpunitCoverage.php
```

6、重构 `ModelShowCommand` 命令
```php
<?php

namespace zxf\Laravel\Modules\Commands\Actions;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Console\ShowModelCommand;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\search;

#[AsCommand('module:model-show', '查看模块或主应用的模型信息')]
class ModelShowCommand extends ShowModelCommand implements PromptsForMissingInput
{
    protected $name = 'module:model-show';

    protected $description = '查看模块或主应用的模型信息 [php artisan module:model-show 模型类 --module=模块名称 --json]';

    protected $signature = 'module:model-show
        {model : 模型名称（不含命名空间）}
        {--module= : 指定模块名称，仅查找该模块 Models}
        {--database= : 指定数据库连接}
        {--json : 以JSON格式输出}';

    /**
     * 查找模型
     */
    public function findModels(string $model): Collection
    {
        $moduleName = $this->option('module');

        // 读取配置
        $modulesPath = config('modules.paths.modules', base_path('Modules'));
        $modelsPath = config('modules.paths.generator.model.path', 'Models');
        $modulesName = config('modules.namespace', 'Modules');

        if ($moduleName) {
            $path = "{$modulesPath}/{$moduleName}/{$modelsPath}";
            $namespace = "{$modulesName}\\{$moduleName}\\{$modelsPath}\\";
        } else {
            $path = app_path($modelsPath);
            $namespace = app()->getNamespace()."{$modelsPath}\\";
        }

        return collect(File::glob("{$path}/{$model}.php"))
            ->map(fn ($file) => $namespace.basename($file, '.php'))
            ->values();
    }

    /**
     * 自动提示模型
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'model' => fn () => search(
                label: '请选择模型',
                options: function (string $search_value) {
                    return $this->findModels(Str::of($search_value)->wrap('*', '*'))->toArray();
                },
                placeholder: '输入模型名称',
                required: '必须选择一个模型',
            ),
        ];
    }

    /**
     * 兼容 Laravel 10 handle
     */
    public function handle(ModelInspector $modelInspector): int
    {
        $model = $this->argument('model');

        if (! Str::contains($model, '\\')) {
            $models = $this->findModels($model);

            if ($models->isEmpty()) {
                $moduleName = $this->option('module');
                // 读取配置
                $modelsPath = config('modules.paths.generator.model.path', 'Models');
                $modulesName = config('modules.namespace', 'Modules');

                if ($moduleName) {
                    $namespace = "{$modulesName}/{$moduleName}/{$modelsPath}";
                } else {
                    $namespace = app()->getNamespace()."{$modelsPath}";
                    // 把 $namespace 里面的 \ 替换为 /
                    $namespace = str_replace('\\', '/', $namespace);
                }

                $this->components->error("未找到模型 [{$namespace}/$model]");

                return self::FAILURE;
            }

            $model = $models->count() === 1
                ? $models->first()
                : $this->components->choice('检测到多个模型，请选择：', $models->toArray());
        }

        $this->input->setArgument('model', $model);

        return parent::handle($modelInspector);
    }
}

```

7、 register里面追加自定义处理

```php
// 注册 whereHasIn 的几个查询方式来替换 whereHas 查询全表扫描的问题
WhereHasInBuilder::register($this);
```

## Commands 多模块命令改造

1、删除部分 Commands

2、改造`src/Module.php` 的 `delete` 方法

```php
把
$result = $this->json()->getFilesystem()->deleteDirectory($this->getPath());
改为
$result = del_dir($this->getPath());
```

3、删除涉及处理 `module.json` 文件的方法
`ModuleGenerator.php` 文件

```
删除 module.json 的相关方法
    generateModuleJsonFile
    cleanModuleJsonFile
    generateResources 中涉及 module.json 那段
```

4、同时创建`Api`和`Web`控制器
`Generators/ModuleGenerator.php` 文件

```
把
if (GenerateConfigReader::read('controller')->generate() === true) {
    $options = $this->type == 'api' ? ['--api' => true] : [];
    $this->console->call('module:make-controller', [
        'controller' => $this->getName().'Controller',
        'module' => $this->getName(),
    ] + $options);
}
更改为
if (GenerateConfigReader::read('controller')->generate() === true) {
    $options = $this->type == 'api' ? ['--api' => true] : [];
    // Web 控制器
    $this->console->call('module:make-controller', [
        'controller' => 'Web/'.$this->getName().'Controller',
        'module' => $this->getName(),
    ] + $options);

    // Api 控制器
    $this->console->call('module:make-controller', [
        'controller' => 'Api/'.$this->getName().'Controller',
        'module' => $this->getName(),
    ] + ['--api' => true]);
}
```

### 更新 `Commands/stubs` 里面的文件

```
stubs/migration/create.stub
stubs/migration/drop.stub
stubs/routes/api.stub
stubs/routes/web.stub
stubs/scaffold/config.stub
stubs/scaffold/provider.stub
stubs/views/
stubs/command.stub
stubs/enum.stub
删除:stubs/package.stub
删除:stubs/json.stub
```

### 删除 `Process` 文件夹

```
先文件夹里面 包含了 Installer、Runner、Updater 三个文件的方法，在「删除部分 Commands」中已经删除了对 Process 的引用，所以可以删除掉
再删除 `Process` 文件夹
```

### 删除 `composer.json`

```
删除:stubs/composer.stub
删除:modules.php 里面所有涉及 composer 的配置
删除: Module.php文件的 getComposerAttr 方法
删除: Generators/ModuleGenerator.php文件的 
    getVendorReplacement、getAuthorNameReplacement、getAuthorEmailReplacement 方法
删除: Commands/Make/ModuleMakeCommand.php文件的链式调用
    ->setVendor($this->option('author-vendor'))
    ->setAuthor($this->option('author-name'), $this->option('author-email'))

```

### 删除 `vite`

```
删除:modules.php 里面所有涉及 vite 的配置
删除:stubs/vite.stub
删除:module_vite 方法
```

## 删除所有`Lumen`相关的文件和文件夹

## [核心]处理多模块中的 Interface

### `FileRepository.php` 文件

改造`scan`方法

```
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
```

把 `has`和`find` 两个方法中的`strtolower`去掉，因为加了之后就判断不了模块了

### 处理 `Activators/FileActivator.php`

```
把涉及 statusesFile 的地方全部删除
删除方法:getStatusesFilePath、config
```

把 `hasStatus` 方法改为直接 `return $status;`

### 处理 `Module.php`

把`get`方法直接改为`return '';`
删除`boot`里面`modules.register`的相关操作和方法
删除方法`isLoadFilesOnBoot`、`registerFiles`
把`getAssets`方法直接改为`return [];`

### 处理 `Json.php`

把`Module.php`的`json`方法直接改为`return [];`
把`Collection.php`的

```php
if ($value instanceof Module) {
    $attributes = $value->json()->getAttributes();
    $attributes['path'] = $value->getPath();

    return $attributes;
}
```

直接改为`return [];`
然后删除 `Json.php` 文件

## 合并到`tools`

1、把`modules.php`配置文件覆盖到 `tools/config/modules.php`

2、把`LaravelModulesServiceProvider.php`覆盖到`src/Laravel/LaravelModulesServiceProvider.php`;注意命名空间

3、把`helpers.php`覆盖到`module_helpers.php`

4、把其他文件和文件夹覆盖到`src/Laravel/Modules`文件夹

5、debug,特别是文件命名空间和路径

6、把本文件复制到`tools`对应文件中
