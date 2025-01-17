<?php

namespace zxf\Laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use zxf\Laravel\Trace\Handle;

class ExtendMiddleware
{
    /** @var $handle Handle */
    protected $handle;

    /**
     * 模块扩展中间件
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->handle = app('trace');

        $this->handle->customRegisterShutdown($request);

        $response = $next($request);

        // 检查响应是否为 BinaryFileResponse 类型（表示文件下载） || 不需要trace处理
        if ($response instanceof BinaryFileResponse || !is_enable_trace()) {
            return $response;
        }

        // 检查响应是否为 JsonResponse 类型（Json 响应）
        // if ($response instanceof JsonResponse) {
        //     return $response;
        // }

        // 在响应发送到浏览器前处理 Trace 内容
        return $this->handle->renderTraceStyleAndScript($request, $response);
    }

    /**
     * 在响应发送到浏览器后处理任务。
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function terminate($request, $response)
    {
        // 测试发现 有时不会执行此方法，因此不能在此做各种「输出」
        return $response;
    }
}
