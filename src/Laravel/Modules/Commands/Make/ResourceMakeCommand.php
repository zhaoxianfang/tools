<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class ResourceMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    protected $name = 'module:make-resource';

    protected $description = '为指定模块创建新的资源类 [php artisan module:make-resource PostResource Blog | php artisan module:make-resource PostResource Blog --collection]';

    public function getDefaultNamespace(): string
    {
        return config('modules.paths.generator.resource.namespace')
            ?? ltrim(config('modules.paths.generator.resource.path', 'Transformers'), config('modules.paths.app_folder', ''));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the resource class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection.'],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $resourcePath = GenerateConfigReader::read('resource');

        return $path.$resourcePath->getPath().'/'.$this->getFileName().'.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    /**
     * Determine if the command is generating a resource collection.
     */
    protected function collection(): bool
    {
        return $this->option('collection') ||
            Str::endsWith($this->argument('name'), 'Collection');
    }

    protected function getStubName(): string
    {
        if ($this->collection()) {
            return '/resource-collection.stub';
        }

        return '/resource.stub';
    }
}
