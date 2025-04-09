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
    protected $description = '将模块的资产发布到应用程序 [php artisan module:publish Blog]';

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
