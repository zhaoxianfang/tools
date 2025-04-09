<?php

namespace zxf\Laravel\Modules\Commands\Publish;

use zxf\Laravel\Modules\Commands\BaseCommand;
use zxf\Laravel\Modules\Publishing\LangPublisher;

class PublishTranslationCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:publish-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将模块的翻译发布到应用程序 [php artisan module:publish-translation Blog]';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $this->components->task("Publishing Translations <fg=cyan;options=bold>{$module->getName()}</> Module", function () use ($module) {
            with(new LangPublisher($module))
                ->setRepository($this->laravel['modules'])
                ->setConsole($this)
                ->publish();
        });
    }

    public function getInfo(): ?string
    {
        return 'Publishing module translations ...';
    }
}
