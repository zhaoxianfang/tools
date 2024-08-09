<?php

namespace zxf\Laravel\Modules\Commands\Publish;

use zxf\Laravel\Modules\Commands\BaseCommand;
use zxf\Laravel\Modules\Publishing\AssetPublisher;

class PublishCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a module\'s assets to the application';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $this->components->task("Publishing Assets <fg=cyan;options=bold>{$module->getName()}</> Module", function () use ($module) {
            with(new AssetPublisher($module))
                ->setRepository($this->laravel['modules'])
                ->setConsole($this)
                ->publish();
        });

    }

    public function getInfo(): ?string
    {
        return 'Publishing module asset files ...';
    }
}
