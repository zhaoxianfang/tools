<?php

namespace zxf\Laravel\Trace\Traits;

use Illuminate\Support\Facades\Response;
use zxf\Laravel\Trace\Handle;

/**
 * 项目die、exit 终止时的处理
 */
trait AppShutdownTrait
{
    public function customRegisterShutdown($request): void
    {
        // 注册 shutdown 函数（只注册一次）
        static $registered = false;
        if (!$registered) {
            $registered = true;

            register_shutdown_function(function () use ($request) {
                // 捕获并获取缓冲区内容
                $output = ob_get_clean();

                // 创建 Laravel 的 Response 对象
                $response = Response::make($output, 200);
                /** @var $trace Handle */
                $trace = app('trace');
                $resp  = $trace->renderTraceStyleAndScript($request, $response);

                // 输出响应内容
                echo $resp->getContent();
                // 终止脚本执行，防止后续内容输出
                exit;
            });
        }
    }
}