<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use zxf\Laravel\Modules\Commands;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (app()->runningInConsole()) {
            // 加载 Modules 模块内的 command
            $this->customAddCommands();
            // 运行在命令行下
            $this->commands(config('modules.commands', self::defaultCommands()->toArray()));
        }
    }

    public function provides(): array
    {
        if (app()->runningInConsole()) {
            // 运行在命令行下
            return self::defaultCommands()->toArray();
        }
        return [];
    }

    /**
     * 自定义 加载/注册 Modules 模块 command
     * 把 Modules 模块下 Console 目录内的 command 全部加载到命令中
     *
     * 创建 模块下的 command : php artisan module:make-command TestCommand Test
     *
     * @return void|boolean
     * @throws \ReflectionException
     */
    protected function customAddCommands()
    {
        $modulesName = modules_name();

        if (!is_dir(base_path($modulesName))) {
            return false;
        }
        $modules = array_slice(scandir(base_path($modulesName)), 2);
        foreach ($modules as $module) {
            $moduleConsolePath = "{$modulesName}/{$module}/Console";
            if (is_dir($paths = base_path($moduleConsolePath))) {
                $namespace = '';
                foreach ((new \Symfony\Component\Finder\Finder)->in($paths)->files() as $command) {
                    $command = $namespace . str_replace(['/', '.php'], ['\\', ''], \Illuminate\Support\Str::after($command->getRealPath(), realpath(base_path()) . DIRECTORY_SEPARATOR));
                    if (is_subclass_of($command, \Illuminate\Console\Command::class) && !(new \ReflectionClass($command))->isAbstract()) {
                        \Illuminate\Console\Application::starting(function ($artisan) use ($command) {
                            $artisan->resolve($command);
                        });
                    }
                }
            }
        }
    }

    /**
     * Get the package default commands.
     */
    public static function defaultCommands(): Collection
    {
        return collect([
            // Actions Commands
            Commands\Actions\CheckLangCommand::class,
            Commands\Actions\DisableCommand::class,
            Commands\Actions\DumpCommand::class,
            Commands\Actions\EnableCommand::class,
            Commands\Actions\InstallCommand::class,
            Commands\Actions\ListCommand::class,
            Commands\Actions\ModelPruneCommand::class,
            Commands\Actions\ModelShowCommand::class,
            Commands\Actions\ModuleDeleteCommand::class,
            Commands\Actions\UnUseCommand::class,
            Commands\Actions\UpdateCommand::class,
            Commands\Actions\UseCommand::class,

            // Database Commands
            Commands\Database\MigrateCommand::class,
            Commands\Database\MigrateRefreshCommand::class,
            Commands\Database\MigrateResetCommand::class,
            Commands\Database\MigrateRollbackCommand::class,
            Commands\Database\MigrateStatusCommand::class,
            Commands\Database\SeedCommand::class,

            // Make Commands
            Commands\Make\ActionMakeCommand::class,
            Commands\Make\CastMakeCommand::class,
            Commands\Make\ChannelMakeCommand::class,
            Commands\Make\ClassMakeCommand::class,
            Commands\Make\CommandMakeCommand::class,
            Commands\Make\ComponentClassMakeCommand::class,
            Commands\Make\ComponentViewMakeCommand::class,
            Commands\Make\ControllerMakeCommand::class,
            Commands\Make\EventMakeCommand::class,
            Commands\Make\EventProviderMakeCommand::class,
            Commands\Make\EnumMakeCommand::class,
            Commands\Make\ExceptionMakeCommand::class,
            Commands\Make\FactoryMakeCommand::class,
            Commands\Make\InterfaceMakeCommand::class,
            Commands\Make\HelperMakeCommand::class,
            Commands\Make\JobMakeCommand::class,
            Commands\Make\ListenerMakeCommand::class,
            Commands\Make\MailMakeCommand::class,
            Commands\Make\MiddlewareMakeCommand::class,
            Commands\Make\MigrationMakeCommand::class,
            Commands\Make\ModelMakeCommand::class,
            Commands\Make\ModuleMakeCommand::class,
            Commands\Make\NotificationMakeCommand::class,
            Commands\Make\ObserverMakeCommand::class,
            Commands\Make\PolicyMakeCommand::class,
            Commands\Make\ProviderMakeCommand::class,
            Commands\Make\RepositoryMakeCommand::class,
            Commands\Make\RequestMakeCommand::class,
            Commands\Make\ResourceMakeCommand::class,
            Commands\Make\RouteProviderMakeCommand::class,
            Commands\Make\RuleMakeCommand::class,
            Commands\Make\ScopeMakeCommand::class,
            Commands\Make\SeedMakeCommand::class,
            Commands\Make\ServiceMakeCommand::class,
            Commands\Make\TraitMakeCommand::class,
            Commands\Make\TestMakeCommand::class,
            Commands\Make\ViewMakeCommand::class,

            //Publish Commands
            Commands\Publish\PublishCommand::class,
            Commands\Publish\PublishConfigurationCommand::class,
            Commands\Publish\PublishMigrationCommand::class,
            Commands\Publish\PublishTranslationCommand::class,

            // Other Commands
            Commands\ComposerUpdateCommand::class,
            Commands\LaravelModulesV6Migrator::class,
            Commands\SetupCommand::class,

            Commands\Database\MigrateFreshCommand::class,
        ]);
    }
}
