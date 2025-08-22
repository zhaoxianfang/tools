<?php

namespace zxf\Laravel\Trace;

use Closure;
use Illuminate\Foundation\Configuration\Exceptions;

class CustomExceptionHandler
{
    /**
     * 初始化 Laravel 11+ 异常处理类
     *
     * @param  Exceptions  $exceptions  lv11 + 异常类
     * @param  Closure|null  $customHandleCallback  想要自定义处理的回调函数：回调 ($code, $message);
     * @param  array  $customHandleCode  需要自定义回调处理的错误码；空表示由接管所有的错误码回调处理，不为空表示只接管指定的错误码回调处理；eg: [401], [401,403]
     * @param  array  $dontReport  不需要被报告的异常类列表
     */
    public static function handle(Exceptions $exceptions, ?Closure $customHandleCallback = null, array $customHandleCode = [], array $dontReport = []): void
    {
        /** @var $trace Handle */
        $trace = app('trace');

        // 去重复报告的异常,确保单个实例的异常只被报告一次
        $exceptions->dontReportDuplicates();

        // 自定义自定义状态码的异常闭包回调处理
        $trace->setCustomCallbackHandel($customHandleCallback, $customHandleCode);
        // 定义不需要被报告的异常
        ! empty($dontReport) && $trace->setDontReport($dontReport);

    }
}
