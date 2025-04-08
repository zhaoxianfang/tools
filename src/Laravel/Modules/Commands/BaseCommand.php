<?php

namespace zxf\Laravel\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
// use Illuminate\Console\Prohibitable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use zxf\Laravel\Modules\Contracts\ConfirmableCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\multiselect;

abstract class BaseCommand extends Command implements PromptsForMissingInput
{
    use ConfirmableTrait;
    // use Prohibitable;

    public const ALL = 'All';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->getDefinition()->addOption(
            option: new InputOption(
                name: strtolower(self::ALL),
                shortcut: 'a',
                mode: InputOption::VALUE_NONE,
                description: 'Check all Modules',
            )
        );

        $this->getDefinition()->addArgument(
            argument: new InputArgument(
                name: 'module',
                mode: InputArgument::IS_ARRAY,
                description: 'The name of module will be used.',
            )
        );

        if ($this instanceof ConfirmableCommand) {
            $this->configureConfirmable();
        }
    }

    abstract public function executeAction($name);

    public function getInfo(): ?string
    {
        return null;
    }

    public function getConfirmableLabel(): ?string
    {
        return 'Application In Production';
    }

    public function getConfirmableCallback(): \Closure|bool|null
    {
        return null;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this instanceof ConfirmableCommand) {
            if (
                // $this->isProhibited() ||
                // ! $this->confirmToProceed($this->getConfirmableLabel(), fn () => true)) {
                ! $this->confirmToProceed($this->getConfirmableLabel(), $this->getConfirmableCallback())) {
                return Command::FAILURE;
            }
        }

        if (! is_null($info = $this->getInfo())) {
            $this->components->info($info);
        }

        $modules = (array) $this->argument('module');

        foreach ($modules as $module) {
            $this->executeAction($module);
        }
    }

    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        $modules = $this->hasOption('direction')
            ? array_keys($this->laravel['modules']->getOrdered($input->hasOption('direction')))
            : array_keys($this->laravel['modules']->all());

        if ($input->getOption(strtolower(self::ALL))) {
            $input->setArgument('module', $modules);

            return;
        }

        if (! empty($input->getArgument('module'))) {
            return;
        }

        $selected_item = multiselect(
            label   : 'Select Modules',
            options : [
                self::ALL,
                ...$modules,
            ],
            required: 'You must select at least one module',
        );

        $input->setArgument(
            'module',
            value: in_array(self::ALL, $selected_item)
                ? $modules
                : $selected_item
        );
    }

    protected function getModuleModel($name)
    {
        return $name instanceof \zxf\Laravel\Modules\Module
            ? $name
            : $this->laravel['modules']->findOrFail($name);
    }

    private function configureConfirmable(): void
    {
        $this->getDefinition()
            ->addOption(
                option: new InputOption(
                    name: 'force',
                    shortcut: null,
                    mode: InputOption::VALUE_NONE,
                    description: 'Force the operation to run without confirmation.',
                )
            );
    }
}
