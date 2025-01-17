<?php

namespace zxf\Laravel\Trace\Traits;

use Illuminate\Support\Facades\Response;
use ReflectionException;
use zxf\Laravel\Trace\Handle;

/**
 * 应用结束时的处理
 */
trait AppEndTrait
{
    /**
     * 应用被 die、exit 终止时的处理
     */
    public function registerShutdownHandle($request): void
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

    /**
     * trace 调试 结束时的处理
     *
     * @param array $traceData trace调试产生的数据
     *
     * @return void
     */
    public function traceEndHandle(array $traceData = []): void
    {
        try {
            if (!empty($handleClass = config('modules.trace_end_handle_class'))) {
                // 检查类是否存在
                if (!class_exists($handleClass)) {
                    return;
                }
                // 检查 $handleClass 类中是否存在 handleTrace 方法
                if (!method_exists($handleClass, 'handleTrace')) {
                    return;
                }
                $callClass = is_string($handleClass) ? new $handleClass() : $handleClass;
                if (!is_callable([$callClass, 'handleTrace'])) {
                    return;
                }
                $callClass->handleTrace($traceData);
            }
        } catch (\Exception $e) {
        }
    }
}