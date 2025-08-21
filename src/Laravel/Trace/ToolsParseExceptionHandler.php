<?php

namespace zxf\Laravel\Trace;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * 处理语法异常错误
 */
class ToolsParseExceptionHandler implements ExceptionHandler
{
    protected ExceptionHandler $handler;
    protected ?Throwable $lastException = null;
    protected bool $rendering = false;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 增强异常报告逻辑
     */
    public function report(Throwable $e): void
    {
        $this->lastException = $e;

        /** @var Handle $handle */
        app('trace')->registerShutdownHandle(Request::instance());
        // 调用原始 report 方法
        $this->handler->report($e);
    }

    /**
     * 增强异常渲染逻辑
     */
    public function render($request, Throwable $e): Response
    {
        // 防止递归调用
        if ($this->rendering) {
            return $this->handler->render($request, $e);
        }

        $this->rendering = true;
        $this->lastException = $e;

        try {
            $response = $this->handler->render($request, $e);

            // 只在最终渲染时处理跟踪信息
            return $this->pringTrace($request, $response);
        } finally {
            $this->rendering = false;
        }
    }

    /**
     * 处理跟踪信息
     */
    protected function pringTrace($request, Response $response): Response
    {
        if ($this->lastException instanceof \ParseError) {
            // 使用反射设置 protected 属性
            $reflection = new \ReflectionClass($response);
            $property = $reflection->getProperty('exception');
            $property->setAccessible(true);
            $property->setValue($response, $this->lastException);

            /** @var Handle $trace */
            $trace = app('trace');
            return $trace->renderTraceStyleAndScript($request, $response);
        }

        return $response;
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->handler->shouldReport($e);
    }

    public function renderForConsole($output, Throwable $e): void
    {
        $this->handler->renderForConsole($output, $e);
    }
}
