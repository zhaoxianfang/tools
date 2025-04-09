<?php

namespace zxf\Laravel\Modules\Commands\Actions;

use zxf\Laravel\Modules\Commands\BaseCommand;
use zxf\Laravel\Modules\Contracts\ConfirmableCommand;

class ModuleDeleteCommand extends BaseCommand implements ConfirmableCommand
{
    protected $name = 'module:delete';

    protected $description = '从应用程序中删除指定模块 [php artisan module:delete Blog]';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);
        $this->components->task("Deleting <fg=cyan;options=bold>{$module->getName()}</> Module", function () use ($module) {
            $module->delete();
        });
    }

    public function getInfo(): ?string
    {
        return 'deleting module ...';
    }

    public function getConfirmableLabel(): string
    {
        return 'Warning: Do you want to remove the module?';
    }

    public function getConfirmableCallback(): \Closure|bool|null
    {
        return true;
    }
}
