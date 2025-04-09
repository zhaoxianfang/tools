<?php

namespace zxf\Laravel\Modules\Commands\Publish;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Commands\BaseCommand;

class PublishConfigurationCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:publish-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将模块的配置文件发布到应用程序 [php artisan module:publish-config Blog]';

    public function executeAction($name): void
    {
        $this->call('vendor:publish', [
            '--provider' => $this->getServiceProviderForModule($name),
            '--force' => $this->option('force'),
            '--tag' => ['config'],
        ]);
    }

    public function getInfo(): ?string
    {
        return 'Publishing module config files ...';
    }

    private function getServiceProviderForModule(string $module): string
    {
        $namespace = $this->laravel['config']->get('modules.namespace');
        $studlyName = Str::studly($module);
        $provider = $this->laravel['config']->get('modules.paths.generator.provider.path');
        $provider = str_replace($this->laravel['config']->get('modules.paths.app_folder'), '', $provider);
        $provider = str_replace('/', '\\', $provider);

        return "$namespace\\$studlyName\\$provider\\{$studlyName}ServiceProvider";
    }

    protected function getOptions(): array
    {
        return [
            ['--force', '-f', InputOption::VALUE_NONE, 'Force the publishing of config files'],
        ];
    }
}
