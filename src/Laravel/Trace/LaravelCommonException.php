<?php

namespace zxf\Laravel\Trace;

use Closure;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;
use zxf\Laravel\Trace\Traits\ExceptionShowDebugHtmlTrait;
use zxf\Laravel\Trace\Traits\ExceptionTrait;

class LaravelCommonException // extends Handler
{
    use ExceptionShowDebugHtmlTrait,ExceptionTrait;

    private static ?Closure $customHandleCallback = null;

    // 需要自定义回调处理的错误码；空表示由本类处理，不为空表示接管指定的错误码回调处理；eg: [401], [401,403]
    private static array $customHandleCode = [];

    // 定义不需要被报告的异常类
    public function getDontReport(): array
    {
        return [
            // \...\xxException::class,
        ];
    }

    /**
     * 初始化 Laravel 11+ 异常处理类
     *
     * @param  Exceptions  $exceptions  lv11 + 异常类
     * @param  Closure|null  $customHandleCallback  想要自定义处理的回调函数(参数3不为空时有效)：回调 ($code, $message);
     * @param  array  $customHandleCode  需要自定义回调处理的错误码；空表示由本类处理，不为空表示接管指定的错误码回调处理；eg: [401], [401,403]
     */
    public static function initLaravelException(Exceptions $exceptions, ?Closure $customHandleCallback = null, array $customHandleCode = []): void
    {
        // 初始化赋值
        self::$customHandleCallback = $customHandleCallback;
        self::$customHandleCode = $customHandleCode;

        // 实例化
        $customException = new static;

        // 去重复报告的异常,确保单个实例的异常只被报告一次
        $exceptions->dontReportDuplicates();

        // 定义不需要被报告的异常
        $exceptions->dontReport($customException->getDontReport());

        // 全局接管所有继承了 \Exception 的异常 报告
        $exceptions->report(function (\Throwable $e) use ($customException) {
            $customException->report($e);
        })->stop(); // 调用 stop() 阻止异常传播到默认的日志记录栈

        // 全局接管所有继承了 \Exception 的异常 渲染
        $exceptions->render(function (\Throwable $e, Request $request) use ($customException) {
            return $customException->render($request, $e);
        });
    }

    /**
     * 报告异常：负责记录异常（后台操作）
     */
    public function report(Throwable $e): void
    {
        // 初始化错误信息
        $this->initError($e);
        // 记录日志
        $this->writeLog($e);
        // dd($e);
    }

    /**
     * 渲染异常为 HTTP 响应：负责显示异常（用户可见的响应）
     */
    public function render($request, Throwable $e): Response|JsonResponse|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (self::$message == '出错啦!') {
            // 可能部分异常不会走 report，例如：abort(401,'...');
            // 手动重新调用  report报告
            $this->report($e);
        }

        if (! empty(self::$customHandleCode) && ! empty(self::$customHandleCallback)) {
            if (in_array(self::$code, self::$customHandleCode)) {
                // 调用自定义处理闭包函数
                return call_user_func(self::$customHandleCallback, self::$code, self::$message);
            }
        }

        // 如果模块下定义了自定义的异常接管类 Handler，则交由模块下的异常类自己处理
        if ($this->hasModuleCustomException()) {
            return $this->handleModulesCustomException($e, $request);
        }

        // 调试模式
        if (config('app.debug')) {
            return $this->debug($e);
        }

        // 判断路径 : 不是get的api 或 json 请求
        if (($request->is('api/*') || ! $request->isMethod('get')) || $request->expectsJson()) {
            return $this->respJson(self::$message, self::$code)->send();
        } else {
            return $this->respView(self::$message, self::$code)->send();
        }
    }

    private function debug(Throwable $e): Response|JsonResponse
    {
        $content = [
            [
                'label' => '异常信息',
                'type' => 'text',
                'value' => self::$isSysErr ? $e->getMessage() : self::$message,
            ], [
                'label' => '状态码',
                'type' => 'text',
                'value' => self::$code,
            ], [
                'label' => '异常文件',
                'type' => 'debug_file',
                'value' => str_replace(base_path(), '', $e->getFile()).':'.$e->getLine().' (行)',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], [
                'label' => '异常代码',
                'type' => 'code',
                'value' => $this->getExceptionContent($e),
            ], [
                'label' => '异常堆栈',
                'type' => 'code',
                'value' => str_replace(base_path(), '', $e->getTraceAsString()),
            ],
        ];

        return $this->outputDebugHtml($content, self::$code.':'.(self::$isSysErr ? $e->getMessage() : self::$message));
    }
}
