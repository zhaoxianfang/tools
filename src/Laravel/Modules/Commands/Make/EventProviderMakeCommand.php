<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class EventProviderMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'module';

    protected $name = 'module:make-event-provider';

    protected $description = '为指定模块创建新的事件服务提供程序类 [php artisan module:make-event-provider EventServiceProvider Blog]';

    public function getDestinationFilePath(): string
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $filePath = GenerateConfigReader::read('provider')->getPath();

        return $path.$filePath.'/'.$this->getEventServiceProviderName().'.php';
    }

    protected function getTemplateContents(): string
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassNameWithoutNamespace(),
        ]))->render();
    }

    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'su.'],
        ];
    }

    protected function getEventServiceProviderName(): array|string
    {
        return Str::studly('EventServiceProvider');
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getEventServiceProviderName());
    }

    public function getDefaultNamespace(): string
    {
        return config('modules.paths.generator.provider.namespace')
            ?? ltrim(config('modules.paths.generator.provider.path', 'Providers'), config('modules.paths.app_folder', ''));
    }

    protected function getStubName(): string
    {
        return '/event-provider.stub';
    }
}
