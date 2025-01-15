<?php

namespace zxf\Laravel\Trace;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

/**
 * 处理语法异常错误
 */
class ToolsParseExceptionHandler implements ExceptionHandler
{
    protected ExceptionHandler $handler;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 增强异常报告逻辑
     *
     * @param Throwable $e
     *
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        // 记录异常前的处理逻辑

        // 调用原始 report 方法
        $this->handler->report($e);
    }

    /**
     * 增强异常渲染逻辑
     *
     * @param           $request
     * @param Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // 开发环境下显示详细错误
        // if (app()->isLocal()) {
        // }

        // 获取当前响应对象
        $response = $this->handler->render($request, $e);

        // 如果是语法错误在此处理（其他类型的错误在中间件中能捕捉到）
        if ($e instanceof \ParseError) {
            set_protected_value($response, 'exception', $e);

            /** @var $trace \zxf\Laravel\Trace\Handle */
            $trace = app('trace');
            return $trace->renderTraceStyleAndScript($request, $response);
        }

        return $response;
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->handler->shouldReport($e);
    }

    public function renderForConsole($output, Throwable $e)
    {
        $this->handler->renderForConsole($output, $e);
    }
}
