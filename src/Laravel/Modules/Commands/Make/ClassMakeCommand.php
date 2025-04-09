<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Support\Str;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class ClassMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:make-class
        {--t|type=class : 类的类型，例如 class, service, repository, contract, etc.}
        {--s|suffix : 创建不带类型后缀的类}
        {--i|invokable : 生成一个可调用的单一方法类}
        {--f|force : 即使类已经存在，也要创建该类}
        {name : 类的名称}
        {module : 目标模块}';

    /**
     * The console command description.
     */
    protected $description = '创建一个新类 [php artisan module:make-class CustomClass Blog]';

    protected $argumentName = 'name';

    public function getTemplateContents(): string
    {
        return (new Stub($this->stub(), [
            'NAMESPACE' => $this->getClassNamespace($this->module()),
            'CLASS' => $this->typeClass(),
        ]))->render();
    }

    public function stub(): string
    {
        return $this->option('invokable') ? '/class-invoke.stub' : '/class.stub';
    }

    public function getDestinationFilePath(): string
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $filePath = GenerateConfigReader::read('class')->getPath() ?? config('modules.paths.app_folder').'Classes';

        return $this->typePath($path.$filePath.'/'.$this->getFileName().'.php');
    }

    protected function getFileName(): string
    {
        $file = Str::studly($this->argument('name'));

        if ($this->option('suffix') === true) {
            $names = [Str::plural($this->type()), Str::singular($this->type())];
            $file = Str::of($file)->remove($names, false);
            $file .= Str::of($this->type())->studly();
        }

        return $file;
    }

    /**
     * Get the type of class e.g. class, service, repository, etc.
     */
    protected function type(): string
    {
        return Str::of($this->option('type'))->remove('=')->singular();
    }

    protected function typePath(string $path): string
    {
        return ($this->type() === 'class') ? $path : Str::of($path)->replaceLast('Classes', Str::of($this->type())->plural()->studly());
    }

    public function typeClass(): string
    {
        return Str::of($this->getFileName())->basename()->studly();
    }

    public function getDefaultNamespace(): string
    {
        $type = $this->type();

        return config("modules.paths.generator.{$type}.namespace", 'Classes');
    }
}
