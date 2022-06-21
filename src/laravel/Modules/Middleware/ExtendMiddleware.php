<?php

namespace zxf\laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExtendMiddleware
{
	protected $traceStr = '';

    /**
     * 模块扩展中间件
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
		$this->traceStr = '';
        if ($request->isMethod('get')) {
            // 监听sql执行
            config('modules.trace') && listan_sql($this->traceStr);
        }

        $response = $next($request);

        return $response;

    }

    /**
     * 在响应发送到浏览器后处理任务。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // 打印sql执行日志
        if ($request->isMethod('get') && config('modules.trace')) {
            echo $this->traceStr;
        }
    }
}
