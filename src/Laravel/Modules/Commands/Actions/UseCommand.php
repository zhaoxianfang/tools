<?php

namespace zxf\Laravel\Modules\Commands\Actions;

use zxf\Laravel\Modules\Commands\BaseCommand;

class UseCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:use';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use the specified module.';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $this->components->task("Using <fg=cyan;options=bold>{$module->getName()}</> Module", function () use ($module) {
            $this->laravel['modules']->setUsed($module);
        });
    }

    public function getInfo(): ?string
    {
        return 'Using Module ...';
    }
}
