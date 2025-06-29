<?php

if (! function_exists('laravel_version')) {
    /**
     * laravel 版本检测
     *
     * @return int|null null:不是laravel, int:laravel版本号
     */
    function laravel_version(): ?int
    {
        return match (true) {
            class_exists('Laravel\Routing\Controller') => 3,
            class_exists('Illuminate\Support\Facades\Facade')
            && ! class_exists('Illuminate\Foundation\Application') => 4,
            ! class_exists('Illuminate\Foundation\Application') => null, // Laravel 5+ 共性类
            method_exists($app = app(), 'version') => (int) explode('.', $app->version())[0],
            defined('Illuminate\Foundation\Application::VERSION') => (int) explode('.', Illuminate\Foundation\Application::VERSION)[0],
            default => null
        };
    }
}

if (! function_exists('is_laravel')) {
    /**
     * 检查运行环境是否 在 laravel 之下
     */
    function is_laravel(): bool
    {
        return ! empty(laravel_version());
    }
}

if (! function_exists('laravel_lt_11')) {
    /**
     * 检查 laravel 大版本是否小于11
     */
    function laravel_lt_11(): bool
    {
        return (int) laravel_version() < 11;
    }
}

if (! function_exists('laravel_egt_11')) {
    /**
     * 检查 laravel 大版本是否大于等于11
     */
    function laravel_egt_11(): bool
    {
        return (int) laravel_version() >= 11;
    }
}

if (! function_exists('is_enable_trace')) {
    /**
     * 判断是否开启 trace 调试
     */
    function is_enable_trace(): bool
    {
        // return !app()->runningInConsole() && !app()->environment('testing') && request()->isMethod('get') && config('modules.trace');
        // return !app()->runningInConsole() && !app()->environment('production') && config('modules.trace') && !request()->expectsJson();
        return ! app()->runningInConsole() && config('modules.trace') && ! is_resource_file(request()->fullUrl(), true);
    }
}

if (empty(laravel_version())) {
    // 如果不是 laravel 框架 环境就停止向后加载
    return false;
}

if (! function_exists('modules_name')) {
    /**
     * 获取多模块的文件夹名称（默认：Modules）
     *
     * @return string 返回配置的模块命名空间或默认值
     */
    function modules_name(): string
    {
        return config('modules.namespace', 'Modules');
    }
}

if (! function_exists('get_module_name')) {
    /**
     * 获取当前所在模块
     *
     * 在 Modules 模块里面 获取当前所在模块名称
     * 注意，需要在 Modules 里面调用，否则返回 App
     *
     * @param  bool  $toUnderlineConvert  是否转换为 驼峰+小写 模式
     * @return mixed|string
     */
    function get_module_name(?bool $toUnderlineConvert = false): mixed
    {
        try {
            if (app()->runningInConsole()) {
                return $toUnderlineConvert ? 'command' : 'Command';
            }
            if (! empty($request = request()) && ! empty($route = $request->route())) {
                $routeNamespace = $route->getAction()['namespace'];
                $modulesNamespaceArr = array_filter(explode('\\', explode('Http\Controllers', $routeNamespace)[0]));
                // 判断 $route->uri() 字符串中是否包含 无路由回调fallback ||
                if (! str_contains($route->uri(), 'fallback') && ! empty($modulesNamespaceArr) && $modulesNamespaceArr[0] == modules_name()) {
                    return $toUnderlineConvert ? underline_convert($modulesNamespaceArr[1]) : $modulesNamespaceArr[1];
                }
            }
            if (! empty($request = request())) {
                // 获取 $request->path() 中第一个 / 之前的字符串
                if ($res = strstr(trim($request->path(), '/'), '/', true)) {
                    return $res;
                }
            }

            return $toUnderlineConvert ? 'app' : 'App';
        } catch (\Exception $err) {
            return get_url_module_name($toUnderlineConvert);
        }
    }
}

if (! function_exists('get_url_module_name')) {
    /**
     * 获取 url 中的模块名称(url前缀模块名称), 例如：http://www.xxx.com/docs/xxx/xxx/xxx 中的 docs
     */
    function get_url_module_name(?bool $toUnderlineConvert = false): string
    {
        $module = str(request()->path())->before('/')->lower()->value() ?: 'app';

        return $toUnderlineConvert ? $module : \Illuminate\Support\Str::studly($module);
    }
}

if (! function_exists('get_user_info')) {
    /**
     * 获取laravel 已经登录的用户信息，没有登录的 返回false
     *
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|int
     */
    function get_user_info(?string $field = null)
    {
        $user = null;
        $authList = config('auth.guards');
        foreach ($authList as $authName => $val) {
            if (auth($authName)->check()) {
                $user = auth($authName)->user();
                break;
            }
        }

        return ! empty($user) ? (empty($field) ? $user : $user[$field]) : null;
    }
}

if (! function_exists('get_laravel_route')) {
    /**
     * 获取 laravel 模块 控制器 方法名
     */
    function get_laravel_route(): array
    {
        $route = request()?->route();

        // 快速返回闭包或命令行情况
        if (! $route || $route->action['uses'] instanceof Closure) {
            return [get_module_name(), 'Closure', 'Closure'];
        }

        // 智能延迟解析（仅在需要时处理）
        $actionName = $route->getActionName();
        $actionMethod = $route->getActionMethod();

        return [
            get_module_name(),
            str($actionName)->beforeLast('@')->classBasename()->toString(),
            $actionMethod === $actionName ? 'Closure' : $actionMethod,
        ];
    }
}

if (! function_exists('listen_sql')) {
    /**
     * 监听sql
     *
     * @param  array|string  $traceLogStrOrArr  监听到的sql 会 通过引用的方式，传递给$traceLogStrOrArr变量
     * @param  bool  $addHtml  是否携带html样式
     * @return void
     *
     * @demo
     *      // 开始监听
     *      listen_sql($logStrOrArr);
     *      // laravel 中间件
     *      $response = $next($request);
     *      // 打印sql追踪
     *      echo $logStr;
     */
    function listen_sql(array|string &$traceLogStrOrArr = '', bool $addHtml = false)
    {
        // 监听sql执行
        $style = $contentStyle = '';
        if ($addHtml) {
            $style = "position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:'微软雅黑';background:#ffffff;";
            $contentStyle = 'overflow:auto;height:auto;padding:0;line-height: 24px;max-height: 200px;';
        }

        $debugInfo = [];
        $logStr = '';

        \Illuminate\Support\Facades\DB::listen(function ($query) use ($style, &$debugInfo, $contentStyle, &$logStr, &$traceLogStrOrArr, $addHtml) {
            $bindings = $query->bindings;
            $sql = $query->sql;
            foreach ($bindings as $replace) {
                $value = is_numeric($replace) ? $replace : "'".$replace."'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            $debugInfo[] = [
                'sql' => $sql,
                'time' => round($query->time / 1000, 3),
            ];
            if ($addHtml) {
                foreach ($debugInfo as $info) {
                    $logStr .= '<li style="border-bottom:1px solid #EEE;font-size:14px;padding:0 12px">'.'execution time:'.$info['time'].'(s);=>sql:'.$info['sql'].'</li>';
                }
                // 打印sql执行日志
                $traceLogStrOrArr = '<div style="'.$style.'"><div style="'.$contentStyle.'">'.$logStr.'</div></div>';
            } else {
                $traceLogStrOrArr = $debugInfo;
            }

        });
    }
}

if (! function_exists('copy_model')) {
    /**
     * 复制一份不带 关联关系的模型
     * 复制模型，主要是解决 laravel -> replicate 复制方法会缺失部分字段问题
     */
    function copy_model($model): mixed
    {
        // 先复制一份模型 // 防止修改到原模型属性
        $obj = $model->replicate();
        // 进行遍历赋值
        $modelObj = collect($model);
        foreach ($modelObj as $key => $item) {
            $obj->$key = $item;
        }
        // 移除所有关联 relations
        $res = $obj->unsetRelations();

        $relations = $model->getRelations();
        foreach ($relations as $real => $v) {
            $res->$real = null;
        }

        return $res;
    }
}

if (! function_exists('module_path')) {
    /**
     * @param  string  $name  模块名称（模块文件夹名称）
     * @param  string|null  $path  路径
     */
    function module_path(string $name, ?string $path = ''): string
    {
        $modulePath = base_path(modules_name().DIRECTORY_SEPARATOR.$name);

        return $modulePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     *
     * @return string
     */
    function config_path(string $path = '')
    {
        return app()->basePath().'/config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     *
     * @return string
     */
    function public_path(string $path = '')
    {
        return app()->make('path.public').($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('trace')) {
    /**
     * 调试代码
     *
     * @param  mixed  ...$args  调试任意个参数
     *                          eg：trace('hello', 'world');
     *                          trace(['hello', 'world']);
     * @return void
     */
    function trace(mixed ...$args)
    {
        /** @var $trace \zxf\Laravel\Trace\Handle */
        $trace = app('trace');
        foreach ($args as $value) {
            $trace->addMessage($value, 'debug');
        }
    }
}

if (! function_exists('view_share')) {
    /**
     * 与所有视图共享数据
     */
    function view_share(string|array $key, mixed $value = ''): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                \Illuminate\Support\Facades\View::share($k, $v);
            }
        } else {
            \Illuminate\Support\Facades\View::share($key, $value);
        }
    }
}

if (! function_exists('get_view_share')) {
    /**
     * 获取所有视图共享的数据 [仅执行本函数之前共享的数据]
     *
     * @param  string  $key  [可选]仅获取某个数据
     */
    function get_view_share(string $key = ''): mixed
    {
        $data = \Illuminate\Support\Facades\View::getShared();
        if (! empty($key)) {
            return $data[$key] ?? null;
        }

        return $data;
    }
}

if (! function_exists('view_exists')) {
    /**
     * 判断视图文件是否存在
     */
    function view_exists($view): bool
    {
        return \Illuminate\Support\Facades\View::exists($view);
    }
}
