<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class ScopeMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    protected $name = 'module:make-scope';

    protected $description = '为指定模块创建新的作用域类 [php artisan module:make-scope ActivePostsScope Blog]';

    public function getDestinationFilePath(): string
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $filePath = GenerateConfigReader::read('scopes')->getPath() ?? config('modules.paths.generator.model.path').'/Scopes';

        return $path.$filePath.'/'.$this->getScopeName().'.php';
    }

    protected function getTemplateContents(): string
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassNameWithoutNamespace(),
        ]))->render();
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the scope class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'su.'],
        ];
    }

    protected function getScopeName(): array|string
    {
        return Str::studly($this->argument('name'));
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getScopeName());
    }

    public function getDefaultNamespace(): string
    {
        $namespace = config('modules.paths.generator.model.path');

        $parts = explode('/', $namespace);
        $models = end($parts);

        return $models.'\Scopes';
    }

    protected function getStubName(): string
    {
        return '/scope.stub';
    }
}
