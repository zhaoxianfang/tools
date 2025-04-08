# 迁移笔记

> 来源：https://github.com/nWidart/laravel-modules

1、替换命名空间

```
把
Nwidart\Modules
全部替换为
zxf\Laravel\Modules
```

2、在 `ConsoleServiceProvider` 里面添加 `customAddCommands`方法

3、FileRepository.php

```
设置 getScanPaths() 方法直接 return [];
改造 scan() 方法
```

4、 使用改造的 `LaravelModulesServiceProvider`文件代替以下文件

```
LaravelModulesServiceProvider
ModulesServiceProvider
```

5、删除`Modules`下的几个文件

```
LumenModulesServiceProvider.php
ModulesServiceProvider.php
LaravelModulesServiceProvider.php
```

6、改造`helpers.php`里面的几个方法

```
module_path()
```

7、核心文件改造

```
在 src/Laravel/Modules/Generators/ModuleGenerator.php 中读取 GenerateConfigReader::read('route-provider') 是否需要创建路由服务提供者的外层判断加上 if(!is_null($routeGeneratorConfig->getPath())) 判断;

把 src/Laravel/Modules/Module.php 的 
public function json($file = null): Json
{
    ...
}
方法直接改为
public function json($file = null): Collection
{
    return Collection::make([]);
}
把 public function delete() 方法里面的
$result = $this->json()->getFilesystem()->deleteDirectory($this->getPath());
改为
$result = del_dir($this->getPath());


把 src/Laravel/Modules/Activators/FileActivator.php 文件：
    getStatusesFilePath() 方法直接删除;
    判断 $this->statusesFile 的方法直接删除;
    $this->modulesStatuses 相关的直接删除;
    $this->cacheKey 相关的直接删除;
    $this->cacheLifetime 相关的直接删除;
    $this->files 相关的直接删除;
    $this->cache 相关的直接删除;
    writeJson() 相关的直接删除;
    flushCache() 相关的直接删除;
    getModulesStatuses() 相关的直接删除;
    config() 相关的直接删除;
    readJson() 方法相关的直接改为 return [] 并删除readJson() 方法;
把 src/Laravel/Modules/Activators/FileActivator.php 中相关的 $this->statusesFile 删除或者返回 '';
把 src/Laravel/Modules/Generators/ModuleGenerator.php 的 generateModuleJsonFile() 方法直接改为 return false;
把 src/Laravel/Modules/Commands/Database/SeedCommand.php 的 getSeederNames() 方法直接改为 return [];
把 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 register() 方法中的`$this->commands(config('modules.commands', self::defaultCommands()->toArray()));`改为 `$this->commands(self::defaultCommands()->toArray());`;
把 src/Laravel/Modules/Process/Updater.php 的 isComposerSilenced() 方法直接改为 return ' --quiet';
把 src/Laravel/Modules/Generators/ModuleGenerator.php 的 generateModuleJsonFile() 方法相关的直接删除;

```

8、`config/modules.php` 文件改造

```
`generator` 里面的 `app/` 前缀都去掉
`replacements.scaffold/config` 里面添加  `, 'LOWER_NAME'`
`generator.lang.path` 设置为  `resources/lang`
`stubs.files` 里面新增
            // 自定义本地化
            'lang/en/messages' => 'resources/lang/en/messages.php',
            'lang/en/validation' => 'resources/lang/en/validation.php',
删除 `stubs.files.composer`、`stubs.files.assets/js/app`、`stubs.files.assets/sass/app`、`stubs.files.vite`、`stubs.files.package`
删除 `stubs.path`
删除 `stubs.enabled`
删除 `stubs.replacements.vite`
删除 `stubs.replacements.json`
删除 `stubs.replacements.composer`
删除 `activators.file.statuses-file`
删除整个 `scan`
删除整个 `commands`
删除整个 `auto-discover`
删除整个 `composer`
删除整个 `activators`

`modules.php` 配置模块里面对应的一级文件夹名称尽量改成首字母大写， 例如 Resources、Config、Migrations、Resources、Routes、Database、Tests、Http、Services、Providers等

```

9、删除无用的 Console 和 对应的方法

```
php artisan module:use 命令
删除 src/Laravel/Modules/Commands/Actions/UseCommand.php
对应 src/Laravel/Modules/FileRepository.php 的 setUsed 方法连同删除
对应 src/Laravel/Modules/Constants/ModuleEvent.php 的 const USED = 'used';
对应 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 Commands\Actions\UnUseCommand::class,
```

```
php artisan module:unuse 命令
删除 src/Laravel/Modules/Commands/Actions/UnUseCommand.php
对应 src/Laravel/Modules/FileRepository.php 的 forgetUsed 方法连同删除
对应 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 Commands\Actions\UseCommand::class,
```

```
php artisan module:enable 命令
删除 src/Laravel/Modules/Commands/Actions/EnableCommand.php
对应 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 Commands\Actions\EnableCommand::class,
```

```
php artisan module:disable 命令
删除 src/Laravel/Modules/Commands/Actions/DisableCommand.php
对应 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 Commands\Actions\DisableCommand::class,
```

```
php artisan module:update 命令(更新模块下的composer等)
删除 src/Laravel/Modules/Commands/Actions/UpdateCommand.php
对应 src/Laravel/Modules/Providers/ConsoleServiceProvider.php 的 Commands\Actions\UpdateCommand::class,
```

10、新增本地化文件夹 `Commands/stubs/lang/`

11、

```
把 src/Laravel/Modules/Activators/FileActivator.php 里面的 内容全部注释
```


把设计到 `src/Laravel/Modules/Contracts/ActivatorInterface.php` 接口里面的方法通通删除 只保留一个 `delete`

`isDisabled`
`setVendor`
`setAuthor`
`src/Contracts/RepositoryInterface.php`