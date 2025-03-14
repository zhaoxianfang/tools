<?php

if (!function_exists('is_laravel')) {
    /**
     * 检查运行环境是否 在 laravel 之下
     *
     * @return bool
     */
    function is_laravel(): bool
    {
        return class_exists('\Illuminate\Support\Facades\DB');
    }
}

if (!function_exists('laravel_lt_11')) {
    /**
     * 检查 laravel 大版本是否小于11
     *
     * @return bool
     */
    function laravel_lt_11(): bool
    {
        return is_laravel() && class_exists(\App\Http\Kernel::class);
    }
}

if (!function_exists('laravel_egt_11')) {
    /**
     * 检查 laravel 大版本是否大于等于11
     *
     * @return bool
     */
    function laravel_egt_11(): bool
    {
        return is_laravel() && !class_exists(\App\Http\Kernel::class);
    }
}

if (!is_laravel()) {
    // 如果不是 laravel 框架 环境就停止向后加载
    return false;
}


if (!function_exists('modules_name')) {
    /**
     * 获取 多模块(默认：Modules) 的文件夹名称
     *
     * @return string
     */
    function modules_name(): string
    {
        return app('config')->get('modules.namespace');
    }
}

if (!function_exists('get_module_name')) {
    /**
     * 获取当前所在模块
     *
     * 在 Modules 模块里面 获取当前所在模块名称
     * 注意，需要在 Modules 里面调用，否则返回 App
     *
     * @param bool $toUnderlineConvert 是否转换为 驼峰+小写 模式
     *
     * @return mixed|string
     */
    function get_module_name(?bool $toUnderlineConvert = false): mixed
    {
        try {
            if (app()->runningInConsole()) {
                return $toUnderlineConvert ? 'command' : 'Command';
            }
            if (!empty($request = request()) && !empty($route = $request->route())) {
                $routeNamespace      = $route->getAction()['namespace'];
                $modulesNamespaceArr = array_filter(explode('\\', explode('Http\Controllers', $routeNamespace)[0]));
                // 判断 $route->uri() 字符串中是否包含 无路由回调fallback ||
                if (!str_contains($route->uri(), 'fallback') && !empty($modulesNamespaceArr) && $modulesNamespaceArr[0] == modules_name()) {
                    return $toUnderlineConvert ? underline_convert($modulesNamespaceArr[1]) : $modulesNamespaceArr[1];
                }
            }
            return $toUnderlineConvert ? 'app' : 'App';
        } catch (\Exception $err) {
            return get_url_module_name($toUnderlineConvert);
        }
    }
}

if (!function_exists('get_url_module_name')) {
    /**
     * 获取 url 中的模块名称(url前缀模块名称), 例如：http://www.xxx.com/docs/xxx/xxx/xxx 中的 docs
     */
    function get_url_module_name(?bool $toUnderlineConvert = false): string
    {
        try {
            $path    = request()->path();
            $pathArr = explode('/', $path);
            return $toUnderlineConvert ? underline_convert($pathArr[0] ?? 'app') : ($pathArr[0] ?? 'App');
        } catch (\Exception $err) {
            return $toUnderlineConvert ? 'app' : 'App';
        }
    }
}

if (!function_exists('get_user_info')) {
    /**
     * 获取laravel 已经登录的用户信息，没有登录的 返回false
     *
     * @param string|null $field
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|int
     */
    function get_user_info(?string $field = null)
    {
        $user     = null;
        $authList = config('auth.guards');
        foreach ($authList as $authName => $val) {
            if (auth($authName)->check()) {
                $user = auth($authName)->user();
                break;
            }
        }
        return !empty($user) ? (empty($field) ? $user : $user[$field]) : null;
    }
}

if (!function_exists('get_laravel_route')) {
    /**
     * 获取 laravel 模块 控制器 方法名
     */
    function get_laravel_route(): array
    {
        // 模块名
        $modules  = get_module_name();
        $hasError = empty($request = request()) || empty($request->route());
        if (php_sapi_name() !== 'cli' && !$hasError) {
            $actionName   = request()->route()->getActionName(); // 获取当前的控制器名称(不带Controller) ,闭包返回 Closure
            $actionMethod = request()->route()->getActionMethod(); // 获取当前方法名称  ,闭包返回 Closure
        } else {
            $actionName   = 'Closure';
            $actionMethod = 'Closure';
        }
        return [$modules, $actionName, $actionMethod];
    }
}

if (!function_exists('listen_sql')) {
    /**
     * 监听sql
     *
     * @param array|string $traceLogStrOrArr 监听到的sql 会 通过引用的方式，传递给$traceLogStrOrArr变量
     * @param bool         $addHtml          是否携带html样式
     *
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
            $style        = "position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:'微软雅黑';background:#ffffff;";
            $contentStyle = 'overflow:auto;height:auto;padding:0;line-height: 24px;max-height: 200px;';
        }

        $debugInfo = [];
        $logStr    = '';

        \Illuminate\Support\Facades\DB::listen(function ($query) use ($style, &$debugInfo, $contentStyle, &$logStr, &$traceLogStrOrArr, $addHtml) {
            $bindings = $query->bindings;
            $sql      = $query->sql;
            foreach ($bindings as $replace) {
                $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                $sql   = preg_replace('/\?/', $value, $sql, 1);
            }

            $debugInfo[] = [
                'sql'  => $sql,
                'time' => round($query->time / 1000, 3),
            ];
            if ($addHtml) {
                foreach ($debugInfo as $info) {
                    $logStr .= '<li style="border-bottom:1px solid #EEE;font-size:14px;padding:0 12px">' . 'execution time:' . $info['time'] . '(s);=>sql:' . $info['sql'] . '</li>';
                }
                // 打印sql执行日志
                $traceLogStrOrArr = '<div style="' . $style . '"><div style="' . $contentStyle . '">' . $logStr . '</div></div>';
            } else {
                $traceLogStrOrArr = $debugInfo;
            }

        });
    }
}

if (!function_exists('copy_model')) {
    /**
     * 复制一份不带 关联关系的模型
     * 复制模型，主要是解决 laravel -> replicate 复制方法会缺失部分字段问题
     *
     * @param $model
     *
     * @return mixed
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

if (!function_exists('is_enable_trace')) {
    /**
     * 判断是否开启 trace 调试
     *
     * @return bool
     */
    function is_enable_trace(): bool
    {
        // return !app()->runningInConsole() && !app()->environment('testing') && request()->isMethod('get') && config('modules.trace');
        // return !app()->runningInConsole() && !app()->environment('production') && config('modules.trace') && !request()->expectsJson();
        return !app()->runningInConsole() && config('modules.trace') && !is_resource_file(request()->fullUrl(), true);
    }
}


if (!function_exists('module_path')) {
    /**
     * @param string      $name 模块名称（模块文件夹名称）
     * @param string|null $path 路径
     *
     * @return string
     */
    function module_path(string $name, ?string $path = ''): string
    {
        $modulePath = base_path(config('modules.namespace') . DIRECTORY_SEPARATOR . $name);
        return $modulePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path(string $path = '')
    {
        return app()->basePath() . '/config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param string $path
     *
     * @return string
     */
    function public_path($path = '')
    {
        return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (!function_exists('module_vite')) {
    /**
     * support for vite
     */
    function module_vite($module, $asset): Illuminate\Foundation\Vite
    {
        return \Illuminate\Support\Facades\Vite::useHotFile(storage_path('vite.hot'))->useBuildDirectory($module)->withEntryPoints([$asset]);
    }
}


if (!function_exists('trace')) {
    /**
     * 调试代码
     *
     * @param mixed ...$args  调试任意个参数
     *                        eg：trace('hello', 'world');
     *                        trace(['hello', 'world']);
     *
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

if (!function_exists('view_share')) {
    /**
     * 与所有视图共享数据
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return void
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

if (!function_exists('get_view_share')) {
    /**
     * 获取所有视图共享的数据 [仅执行本函数之前共享的数据]
     *
     * @param string $key [可选]仅获取某个数据
     *
     * @return mixed
     */
    function get_view_share(string $key = ''): mixed
    {
        $data = \Illuminate\Support\Facades\View::getShared();
        if (!empty($key)) {
            return $data[$key] ?? null;
        }
        return $data;
    }
}

if (!function_exists('view_exists')) {
    /**
     * 判断视图文件是否存在
     *
     * @param $view
     *
     * @return bool
     */
    function view_exists($view): bool
    {
        return \Illuminate\Support\Facades\View::exists($view);
    }
}