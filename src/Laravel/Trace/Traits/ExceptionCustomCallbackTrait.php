<?php

namespace zxf\Laravel\Trace\Traits;

use Closure;
use Throwable;

/**
 * 开发者自定义异常回调处理Trait
 */
trait ExceptionCustomCallbackTrait
{
    private ?Closure $customHandleCallback = null;

    // 需要自定义回调处理的错误码；空表示由本类处理，不为空表示接管指定的错误码回调处理；eg: [401], [401,403]
    private array $customHandleCode = [];

    // 定义不需要被报告的异常
    public array $dontReport = [];

    /**
     * 初始化 Laravel 11+ 异常处理类
     *
     * @param  Closure|null  $customHandleCallback  想要自定义处理的回调函数(参数3不为空时有效)：回调 ($code, $message);
     * @param  array  $customHandleCode  需要自定义回调处理的错误码；空表示由本类处理，不为空表示接管指定的错误码回调处理；eg: [401], [401,403]
     */
    public function setCustomCallbackHandel(?Closure $customHandleCallback = null, array $customHandleCode = []): void
    {
        $this->customHandleCallback = $customHandleCallback;
        $this->customHandleCode = $customHandleCode;
    }

    // 设置不需要被报告的异常
    public function setDontReport(array $dontReport = []): void
    {
        $this->dontReport = $dontReport;
    }

    /**
     * 运行自定义闭包回调处理
     */
    public function runCallbackHandle(Throwable $e)
    {
        if (! empty($this->customHandleCallback) && $this->customHandleCallback instanceof Closure) {
            if (! empty($this->customHandleCode)) {
                if (in_array(self::$code, $this->customHandleCode)) {
                    // 调用自定义处理闭包函数
                    return call_user_func($this->customHandleCallback, self::$code, self::$message, $e);
                }
            } else {
                // 调用自定义处理闭包函数
                return call_user_func($this->customHandleCallback, self::$code, self::$message, $e);
            }
        }

        return null;
    }
}
