<?php

namespace zxf\Laravel\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use zxf\Laravel\Modules\Commands;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        Commands\CommandMakeCommand::class,
        Commands\ControllerMakeCommand::class,
        Commands\DumpCommand::class,
        Commands\EventMakeCommand::class,
        Commands\JobMakeCommand::class,
        Commands\ListenerMakeCommand::class,
        Commands\MailMakeCommand::class,
        Commands\MiddlewareMakeCommand::class,
        Commands\NotificationMakeCommand::class,
        Commands\ProviderMakeCommand::class,
        Commands\RouteProviderMakeCommand::class,
        Commands\InstallCommand::class,
        Commands\ModuleDeleteCommand::class,
        Commands\ModuleMakeCommand::class,
        Commands\FactoryMakeCommand::class,
        Commands\PolicyMakeCommand::class,
        Commands\RequestMakeCommand::class,
        Commands\RuleMakeCommand::class,
        Commands\MigrateCommand::class,
        Commands\MigrateRefreshCommand::class,
        Commands\MigrateResetCommand::class,
        Commands\MigrateFreshCommand::class,
        Commands\MigrateRollbackCommand::class,
        Commands\MigrateStatusCommand::class,
        Commands\MigrationMakeCommand::class,
        Commands\ModelMakeCommand::class,
        Commands\PublishCommand::class,
        Commands\PublishConfigurationCommand::class,
        Commands\PublishTranslationCommand::class,
        Commands\SeedCommand::class,
        Commands\SeedMakeCommand::class,
        Commands\SetupCommand::class,
        Commands\UpdateCommand::class,
        Commands\ResourceMakeCommand::class,
        Commands\TestMakeCommand::class,
        Commands\LaravelModulesV6Migrator::class,
        Commands\ComponentClassMakeCommand::class,
        Commands\ComponentViewMakeCommand::class,
    ];

    public function register(): void
    {
        if (app()->runningInConsole()) {
            // 运行在命令行下
            $this->commands($this->commands);

            // 加载 Modules 模块内的 command
            $this->customAddCommands();
        }
    }

    public function provides(): array
    {
        if (app()->runningInConsole()) {
            return $this->commands;
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
}
