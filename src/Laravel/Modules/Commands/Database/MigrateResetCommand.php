<?php

namespace zxf\Laravel\Modules\Commands\Database;

use zxf\Laravel\Modules\Commands\BaseCommand;
use zxf\Laravel\Modules\Contracts\ConfirmableCommand;
use zxf\Laravel\Modules\Migrations\Migrator;
use zxf\Laravel\Modules\Traits\MigrationLoaderTrait;
use Symfony\Component\Console\Input\InputOption;

class MigrateResetCommand extends BaseCommand implements ConfirmableCommand
{
    use MigrationLoaderTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置模块迁移 [php artisan module:migrate-reset Blog]';

    public function executeAction($name): void
    {
        $module = $this->getModuleModel($name);

        $migrator = new Migrator($module, $this->getLaravel());

        $database = $this->option('database');

        if (! empty($database)) {
            $migrator->setDatabase($database);
        }

        $migrated = $migrator->reset();

        if (count($migrated)) {
            foreach ($migrated as $migration) {
                $this->line("Rollback: <info>{$migration}</info>");
            }

            return;
        }

        $this->components->warn("Nothing to rollback on module <fg=cyan;options=bold>{$module->getName()}</>");
    }

    public function getInfo(): ?string
    {
        return null;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['direction', 'd', InputOption::VALUE_OPTIONAL, 'The direction of ordering.', 'desc'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
        ];
    }
}
