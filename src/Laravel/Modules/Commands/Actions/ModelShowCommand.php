<?php

namespace zxf\Laravel\Modules\Commands\Actions;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Console\ShowModelCommand;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\search;

#[AsCommand('module:model-show', '查看模块或主应用的模型信息')]
class ModelShowCommand extends ShowModelCommand implements PromptsForMissingInput
{
    protected $name = 'module:model-show';

    protected $description = '查看模块或主应用的模型信息 [php artisan module:model-show 模型类名 --module=模块名称 --json]';

    protected $signature = 'module:model-show
        {model : 模型名称（不含命名空间）}
        {--module= : 指定模块名称，仅查找该模块 Models}
        {--database= : 指定数据库连接}
        {--json : 以JSON格式输出}';

    /**
     * 查找模型
     */
    public function findModels(string $model): Collection
    {
        $moduleName = $this->option('module');

        // 读取配置
        $modulesPath = config('modules.paths.modules', base_path('Modules'));
        $modelsPath = config('modules.paths.generator.model.path', 'Models');
        $modulesName = config('modules.namespace', 'Modules');

        if ($moduleName) {
            $path = "{$modulesPath}/{$moduleName}/{$modelsPath}";
            $namespace = "{$modulesName}\\{$moduleName}\\{$modelsPath}\\";
        } else {
            $path = app_path($modelsPath);
            $namespace = app()->getNamespace()."{$modelsPath}\\";
        }

        return collect(File::glob("{$path}/{$model}.php"))
            ->map(fn ($file) => $namespace.basename($file, '.php'))
            ->values();
    }

    /**
     * 自动提示模型
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'model' => fn () => search(
                label: '请选择模型',
                options: function (string $search_value) {
                    return $this->findModels(Str::of($search_value)->wrap('*', '*'))->toArray();
                },
                placeholder: '输入模型名称',
                required: '必须选择一个模型',
            ),
        ];
    }

    /**
     * 兼容 Laravel 10 handle
     */
    public function handle(ModelInspector $modelInspector): int
    {
        $model = $this->argument('model');

        if (! Str::contains($model, '\\')) {
            $models = $this->findModels($model);

            if ($models->isEmpty()) {
                $moduleName = $this->option('module');
                // 读取配置
                $modelsPath = config('modules.paths.generator.model.path', 'Models');
                $modulesName = config('modules.namespace', 'Modules');

                if ($moduleName) {
                    $namespace = "{$modulesName}/{$moduleName}/{$modelsPath}";
                } else {
                    $namespace = app()->getNamespace()."{$modelsPath}";
                    // 把 $namespace 里面的 \ 替换为 /
                    $namespace = str_replace('\\', '/', $namespace);
                }

                $this->components->error("未找到模型 [{$namespace}/$model]");

                return self::FAILURE;
            }

            $model = $models->count() === 1
                ? $models->first()
                : $this->components->choice('检测到多个模型，请选择：', $models->toArray());
        }

        $this->input->setArgument('model', $model);

        return parent::handle($modelInspector);
    }
}
