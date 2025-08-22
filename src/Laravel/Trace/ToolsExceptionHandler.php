<?php

namespace zxf\Laravel\Trace;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * 处理异常错误
 */
class ToolsExceptionHandler implements ExceptionHandler
{
    protected ExceptionHandler $handler;

    protected ?Throwable $lastException = null;

    protected bool $rendering = false;

    protected int $maxReportedExceptions = 100; // 防止内存泄漏

    // 添加异常跟踪数组，用于去重
    protected array $reportedHashes = [];

    protected Handle $trace;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;

        $this->trace = app('trace');
    }

    /**
     * 报告异常（增强异常报告逻辑）：负责记录异常（后台操作） ；
     */
    public function report(Throwable $e): void
    {
        $exceptionHash = $this->getExceptionHash($e);

        // 定义为不需要被报告的异常 || 检查是否已经报告过
        if ($this->shouldntReport($e) || $this->hasReported($exceptionHash)) {
            return;
        }

        // 防止内存泄漏，限制存储的异常数量
        $this->cleanupReportedExceptions();

        // 标记为已报告
        $this->reportedHashes[$exceptionHash] = microtime(true);

        $this->lastException = $e;

        try {
            // 执行跟踪相关的预处理
            $this->beforeReport($e);

            // 初始化错误信息
            $this->trace->initError($e);
            // 记录日志
            $this->trace->writeLog($e);

            // 调用原始 report 方法
            $this->handler->report($e);

            // 执行报告后的处理
            $this->afterReport($e);

        } catch (Throwable $reportError) {
            // 避免报告过程中的异常导致无限循环
            // 记录日志
            $this->trace->writeLog($reportError);
        }
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

        if ($this->trace::$message == '出错啦!') {
            // 可能部分异常不会走 report，例如：abort(401,'...');
            // 手动重新调用  report报告
            $this->trace->initError($e);
        }

        // 运行自定义闭包回调
        $this->trace->runCallbackHandle();

        try {
            // 如果模块下定义了自定义的异常接管类 Handler，则交由模块下的异常类自己处理
            if ($this->trace->hasModuleCustomException()) {
                return $this->trace->handleModulesCustomException($e, $request);
            }
        } catch (Throwable $e) {
            // 可能自定义接管的异常类也有异常
        }

        // 调试模式
        if (config('app.debug') || app()->runningInConsole()) {
            // return $this->trace->debug($e);

            try {
                $response = $this->handler->render($request, $e);
                if (app()->runningInConsole() || app()->runningUnitTests()) {
                    return $response;
                }

                // 只在最终渲染时处理跟踪信息
                return $this->pringTrace($request, $response);
            } finally {
                $this->rendering = false;
            }
        }

        // 判断路径 : 不是get的api 或 json 请求
        if (($request->is('api/*') || ! $request->isMethod('get')) || $request->expectsJson()) {
            return $this->trace->respJson($this->trace::$message, $this->trace::$code)->send();
        } else {
            return $this->trace->respView($this->trace::$message, $this->trace::$code)->send();
        }
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->handler->shouldReport($e);
    }

    public function renderForConsole($output, Throwable $e): void
    {
        $this->handler->renderForConsole($output, $e);
    }

    /**
     * 检查异常是否在不报告列表中
     */
    protected function shouldntReport(Throwable $e): bool
    {
        foreach ($this->trace->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * 报告前的预处理
     */
    protected function beforeReport(Throwable $e): void
    {
        try {
            // 不是生产环境，且已绑定了 trace 组件，则注册 shutdown 钩子
            // if (app()->bound('trace') && Request::hasMacro('instance') && ! app()->isProduction()) {
            if (app()->bound('trace') && Request::hasMacro('instance')) {
                $this->trace->registerShutdownHandle(Request::instance());
            }
        } catch (Throwable $traceError) {
            // 静默处理跟踪错误，不影响主要异常报告流程
            // 记录日志
            $this->trace->writeLog($traceError);
        }
    }

    /**
     * 报告后的处理
     */
    protected function afterReport(Throwable $e): void
    {
        // 可以在这里添加报告后的额外处理逻辑
    }

    /**
     * 处理跟踪信息
     */
    protected function pringTrace($request, Response $response): Response
    {
        if ($this->lastException instanceof \ParseError) {
            set_protected_value($response, 'exception', $this->lastException);

            return $this->trace->renderTraceStyleAndScript($request, $response);
        }

        return $response;
    }

    /**
     * 生成异常的唯一哈希
     */
    protected function getExceptionHash(Throwable $e): string
    {
        return md5(
            get_class($e).
            $e->getFile().
            $e->getLine().
            $e->getMessage().
            $e->getCode()
        );
    }

    /**
     * 检查异常是否已经报告过
     */
    protected function hasReported(string $exceptionHash): bool
    {
        return isset($this->reportedHashes[$exceptionHash]);
    }

    /**
     * 清理已报告的异常记录，防止内存泄漏
     */
    protected function cleanupReportedExceptions(): void
    {
        if (count($this->reportedHashes) > $this->maxReportedExceptions) {
            // 保留最近的一半记录
            $half = (int) ($this->maxReportedExceptions / 2);
            $this->reportedHashes = array_slice(
                $this->reportedHashes,
                -$half,
                $half,
                true
            );
        }

        // 可选：清理超过一定时间的记录（例如1小时）
        $oneHourAgo = microtime(true) - 3600;
        $this->reportedHashes = array_filter(
            $this->reportedHashes,
            fn ($timestamp) => $timestamp > $oneHourAgo
        );
    }

    /**
     * 获取已报告异常的数量（用于监控）
     */
    public function getReportedCount(): int
    {
        return count($this->reportedHashes);
    }

    /**
     * 清空已报告的异常记录
     */
    public function clearReportedExceptions(): void
    {
        $this->reportedHashes = [];
    }

    /**
     * 析构函数 - 清理资源
     */
    public function __destruct()
    {
        // 清空大数组，帮助GC
        $this->reportedHashes = [];
        $this->lastException = null;
    }
}
