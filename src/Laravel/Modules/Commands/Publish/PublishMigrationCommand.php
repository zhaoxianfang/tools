<?php

namespace zxf\Laravel\Modules\Commands\Publish;

use zxf\Laravel\Modules\Commands\BaseCommand;
use zxf\Laravel\Modules\Migrations\Migrator;
use zxf\Laravel\Modules\Publishing\MigrationPublisher;

class PublishMigrationCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:publish-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Publish a module's migrations to the application";

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $this->components->task("Publishing Migration <fg=cyan;options=bold>{$module->getName()}</> Module", function () use ($module) {
            with(new MigrationPublisher(new Migrator($module, $this->getLaravel())))
                ->setRepository($this->laravel['modules'])
                ->setConsole($this)
                ->publish();
        });
    }

    public function getInfo(): ?string
    {
        return 'Publishing module migrations ...';
    }
}
