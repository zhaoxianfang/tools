<?php

namespace zxf\Laravel\Trace;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * 处理语法异常错误
 */
class ToolsParseExceptionHandler implements ExceptionHandler
{
    protected ExceptionHandler $handler;

    protected $e;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 增强异常报告逻辑
     *
     *
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        // 记录异常前的处理逻辑
        $this->e = $e;

        /** @var $handle Handle */
        app('trace')->registerShutdownHandle(Request::instance());

        // 调用原始 report 方法
        $this->handler->report($e);
    }

    /**
     * 增强异常渲染逻辑
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $this->e = $e;

        // 开发环境下显示详细错误
        // if (app()->isLocal()) {
        // }

        // 获取当前响应对象
        $response = $this->handler->render($request, $e);

        return $this->pringTrace($request, $response);
    }

    public function pringTrace($request, $response)
    {
        // 如果是语法错误在此处理（其他类型的错误在中间件中能捕捉到）
        if ($this->e instanceof \ParseError) {
            set_protected_value($response, 'exception', $this->e);

            /** @var $trace Handle */
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
