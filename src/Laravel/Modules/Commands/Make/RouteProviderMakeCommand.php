<?php

namespace zxf\Laravel\Modules\Commands\Make;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use zxf\Laravel\Modules\Support\Config\GenerateConfigReader;
use zxf\Laravel\Modules\Support\Stub;
use zxf\Laravel\Modules\Traits\ModuleCommandTrait;

class RouteProviderMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'module';

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'module:route-provider';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = '为指定模块创建新的路由服务提供程序 [php artisan module:route-provider Blog]';

    /**
     * The command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'],
        ];
    }

    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/route-provider.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getFileName(),
            'MODULE_NAMESPACE' => $this->laravel['modules']->config('namespace'),
            'MODULE' => $this->getModuleName(),
            'CONTROLLER_NAMESPACE' => $this->getControllerNameSpace(),
            'WEB_ROUTES_PATH' => $this->getWebRoutesPath(),
            'API_ROUTES_PATH' => $this->getApiRoutesPath(),
            'LOWER_NAME' => $module->getLowerName(),
            'KEBAB_NAME' => $module->getKebabName(),
        ]))->render();
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return 'RouteServiceProvider';
    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('provider');

        return $path.$generatorPath->getPath().'/'.$this->getFileName().'.php';
    }

    /**
     * @return mixed
     */
    protected function getWebRoutesPath()
    {
        return '/'.$this->laravel['modules']->config('stubs.files.routes/web', 'Routes/web.php');
    }

    /**
     * @return mixed
     */
    protected function getApiRoutesPath()
    {
        return '/'.$this->laravel['modules']->config('stubs.files.routes/api', 'Routes/api.php');
    }

    public function getDefaultNamespace(): string
    {
        return config('modules.paths.generator.provider.namespace')
            ?? ltrim(config('modules.paths.generator.provider.path', 'Providers'), config('modules.paths.app_folder', ''));
    }

    private function getControllerNameSpace(): string
    {
        $module = $this->laravel['modules'];

        return str_replace('/', '\\', $module->config('paths.generator.controller.namespace') ?: $module->config('paths.generator.controller.path', 'Controller'));
    }
}
