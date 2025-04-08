<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Generators\ModuleGenerator;

class ModuleMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $names = $this->argument('name');
        $success = true;

        foreach ($names as $name) {
            $code = with(new ModuleGenerator($name))
                ->setFilesystem($this->laravel['files'])
                ->setModule($this->laravel['modules'])
                ->setConfig($this->laravel['config'])
                ->setConsole($this)
                ->setComponent($this->components)
                ->setForce($this->option('force'))
                ->setType($this->getModuleType())
                ->generate();

            if ($code === E_ERROR) {
                $success = false;
            }
        }

        // to discover new service providers
        Process::path(base_path())
            ->command('composer dump-autoload')
            ->run()->output();

        return $success ? 0 : E_ERROR;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::IS_ARRAY, 'The names of modules will be created.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain module (without some resources).'],
            ['api', null, InputOption::VALUE_NONE, 'Generate an api module.'],
            ['web', null, InputOption::VALUE_NONE, 'Generate a web module.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the module already exists.'],
            ['author-name', null, InputOption::VALUE_OPTIONAL, 'Author name.'],
            ['author-email', null, InputOption::VALUE_OPTIONAL, 'Author email.'],
            ['author-vendor', null, InputOption::VALUE_OPTIONAL, 'Author vendor.'],
        ];
    }

    /**
     * Get module type .
     *
     * @return string
     */
    private function getModuleType()
    {
        $isPlain = $this->option('plain');
        $isApi = $this->option('api');

        if ($isPlain && $isApi) {
            return 'web';
        }
        if ($isPlain) {
            return 'plain';
        } elseif ($isApi) {
            return 'api';
        } else {
            return 'web';
        }
    }
}
