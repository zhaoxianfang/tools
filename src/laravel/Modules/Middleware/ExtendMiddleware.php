<?php

namespace zxf\laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use zxf\laravel\Trace\Handle;

class ExtendMiddleware
{
    /**
     * 模块扩展中间件
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $traceHandle = '';
        if (!app()->runningInConsole() && $request->isMethod('get') && config('modules.trace')) {
            $traceHandle = (new Handle($request))->handle();
        }

        $response = $next($request);

        // 打印sql执行日志
        if (!app()->runningInConsole() && $request->isMethod('get') && config('modules.trace')) {
            $traceContent = $traceHandle->output();

            $pageContent = get_protected_value($response, 'content');
            $position    = strripos($pageContent, "</body>");
            $pageContent = substr_replace($pageContent, $traceContent . PHP_EOL, $position, 0);
            set_protected_value($response, 'content', $pageContent);
        }

        return $response;

    }

    /**
     * 在响应发送到浏览器后处理任务。
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // 测试发现 有时不会执行此方法，因此不能在此做各种「输出」
        return $response;
    }
}
