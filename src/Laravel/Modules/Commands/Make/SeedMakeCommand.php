<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\CanClearModulesCache;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class SeedMakeCommand extends GeneratorCommand
{
    use CanClearModulesCache;
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    /**
     * The console command name.
     */
    protected $name = 'module:make-seed';

    /**
     * The console command description.
     */
    protected $description = '为指定模块创建新的种子程序 [php artisan module:make-seed seed_fake_blog_posts Blog]';

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of seeder will be created.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            [
                'master',
                null,
                InputOption::VALUE_NONE,
                'Indicates the seeder will created is a master database seeder.',
            ],
        ];
    }

    protected function getTemplateContents(): mixed
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/seeder.stub', [
            'NAME' => $this->getSeederName(),
            'MODULE' => $this->getModuleName(),
            'NAMESPACE' => $this->getClassNamespace($module),

        ]))->render();
    }

    protected function getDestinationFilePath(): mixed
    {
        $this->clearCache();

        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $seederPath = GenerateConfigReader::read('seeder');

        return $path.$seederPath->getPath().'/'.$this->getSeederName().'.php';
    }

    /**
     * Get the seeder name.
     */
    private function getSeederName(): string
    {
        $string = $this->argument('name');
        $string .= $this->option('master') ? 'Database' : '';
        $suffix = 'Seeder';

        if (strpos($string, $suffix) === false) {
            $string .= $suffix;
        }

        return Str::studly($string);
    }

    /**
     * Get default namespace.
     */
    public function getDefaultNamespace(): string
    {
        return config('modules.paths.generator.seeder.namespace')
            ?? ltrim(config('modules.paths.generator.seeder.path', 'Database/Seeders'), config('modules.paths.app_folder', ''));
    }
}
