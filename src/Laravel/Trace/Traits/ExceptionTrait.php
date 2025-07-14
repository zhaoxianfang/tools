<?php

namespace zxf\Laravel\Trace\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 异常处理Trait
 */
trait ExceptionTrait
{
    use ExceptionCodeTrait,ExceptionNotifyTrait;

    // 可使外部调用的处理好的错误码
    public static int $code = 500;

    // 可使外部调用的处理好的错误信息
    public static string $message = '出错啦!';

    // 是否为系统错误
    public static bool $isSysErr = false;

    // 错误信息
    public static array $content = [];

    public function initError(Throwable $e): void
    {
        self::$isSysErr = self::isSystemException($e);
        $this->setStatusCode($e);
        $this->setErrorMessage($e);
        $this->setError($e);
    }

    // 写入错误日志
    public function writeLog(Throwable $e): void
    {
        $message = self::$isSysErr ? $e->getMessage() : self::$message;

        // 标记日志
        try {
            Log::error('[异常]:'.$message, self::$content);
        } catch (Throwable $err) {
            // 写入本地文件日志
            Log::channel('stack')->error('[异常]:'.$message, self::$content);
        }
    }

    /**
     * 获取 HTTP 状态码
     */
    protected function setStatusCode(Throwable $e): int
    {
        // 特定异常的状态码映射
        self::$code = match (true) {
            $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface => $e->getStatusCode(),// 如果是 HTTP 异常，使用其状态码
            $e instanceof \Illuminate\Auth\AuthenticationException => 401,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
            $e instanceof \Illuminate\Validation\ValidationException => 422,
            $e instanceof \Illuminate\Database\QueryException && str_contains($e->getMessage(), 'Duplicate entry') => 409,
            default => $e->getCode() > 0 ? (int) $e->getCode() : (int) (method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500),
        };

        return self::$code;
    }

    /**
     * 获取用户友好的错误信息
     */
    protected function setErrorMessage(Throwable $e): string
    {
        if (App::environment('production') || ! config('app.debug') || self::$isSysErr) {
            // 生产环境 || 关闭调试 || 系统错误 => 返回错误码对应的提示信息
            self::$message = $this->getCodeMeg(self::$code);
        } else {
            self::$message = $e->getMessage();
        }

        return self::$message;
    }

    protected function setError(Throwable $e): array
    {
        self::$content = [
            'message:' => self::$message,   // 返回用户自定义的异常信息
            'code:' => self::$code,      // 返回用户自定义的异常代码
            'file:' => $e->getFile(),      // 返回发生异常的PHP程序文件名
            'line:' => $e->getLine(),        // 返回发生异常的代码所在行的行号
            // "trace:"     => $err->getTrace(),      //返回发生异常的传递路线
            // "传递路线String" => $err->getTraceAsString(),//返回发生异常的传递路线
        ];

        return self::$content;
    }

    /**
     * 判断是否为系统错误
     */
    private static function isSystemException(Throwable $exception): bool
    {
        // zxf/tools 扩展包中的错误不算系统错误
        $filePath = $exception->getFile();
        if (str_contains($filePath, 'zxf/tools') || str_contains($filePath, 'zxf\tools')) {
            return false;
        }

        // 致命错误
        if (self::isFatalError($exception)) {
            // 判断是否为致命错误
            return true;
        }

        return match (true) {
            $exception instanceof \Modules\Core\Exceptions\UserException => false, // 自定义错误
            $exception instanceof \ErrorException => true, // 运行时错误
            $exception instanceof \Illuminate\Database\QueryException => true, // 数据库查询错误
            $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => true, // 模型未找到
            $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => true, // 路由未找到
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException => true, // 通用HTTP错误
            $exception instanceof \BadMethodCallException => true, // 当调度的操作方法不存在时
            $exception instanceof \JsonException => true, // json 编码、解码错误
            $exception instanceof \Symfony\Component\HttpFoundation\File\Exception\FileException => false, // 文件上传错误
            $exception instanceof \Illuminate\Validation\ValidationException => false, // 验证错误
            $exception instanceof \Illuminate\Auth\AuthenticationException => false, // 认证错误
            $exception instanceof \Illuminate\Auth\Access\AuthorizationException => false, // 未授权的 Policy 错误
            $exception instanceof \Illuminate\Session\TokenMismatchException => false, // 当 CSRF 令牌不匹配,token 错误
            default => false,
        };
    }

    /**
     * 判断是否为致命错误
     */
    protected static function isFatalError(Throwable $exception): bool
    {
        return
            $exception instanceof \ParseError // 语法错误，如语法拼写不正确。
            // || $exception instanceof \Error
            || $exception instanceof \TypeError // 类型错误，例如传递的参数类型不符合预期
            || $exception instanceof \DivisionByZeroError // 除以零的错误
            || $exception instanceof \AssertionError // 断言失败的错误
            || $exception instanceof \Symfony\Component\ErrorHandler\Error\FatalError; // 致命错误
        // || $exception instanceof \RuntimeException // 运行时异常，比如操作系统相关错误
    }

    // 是否自定义模块异常接管类
    private function hasModuleCustomException(): bool
    {
        $modulesExceptions = config('modules.namespace').'\\'.$this->getModuleName().'\Exceptions\Handler';

        return class_exists($modulesExceptions) && method_exists($modulesExceptions, 'render');
    }

    // 模块下自定义的异常接管类
    private function handleModulesCustomException(Throwable $e, $request)
    {
        // 如果模块下定义了自定义的异常接管类 Handler，则交由模块下的异常类自己处理
        $modulesExceptions = config('modules.namespace').'\\'.$this->getModuleName().'\Exceptions\Handler';
        if (class_exists($modulesExceptions) && method_exists($modulesExceptions, 'render')) {
            try {
                if (collect($customRes = call_user_func_array([new $modulesExceptions, 'render'], [$request, $e]))->isNotEmpty()) {
                    if (is_string($customRes)) {
                        $this->showExitMessage($e);
                    }

                    return $customRes;
                }
            } catch (\Exception $err) {
                // 记录错误日志
                $this->writeLog($err);

                // 运行到此处，大概率无法进行响应了, 直接终止运行
                $this->showExitMessage($e);
            }
        }
    }

    private function getModuleName(): string
    {
        $moduleName = get_module_name();
        if (empty($moduleName) || strtolower($moduleName) == 'app') {
            $moduleName = get_url_module_name();
        }

        return ucwords($moduleName);
    }

    /**
     * 获取异常代码片段
     *
     * @return false|string
     */
    private function getExceptionContent(Throwable $e)
    {
        $startLine = $e->getLine() - 4;
        $endLine = $e->getLine() + 4;
        $filePath = $e->getFile();

        // 检查行号是否合理
        if (! is_int($startLine) || ! is_int($endLine) || $startLine <= 0 || $endLine < $startLine) {
            return false;
        }

        // 初始化结果数组和当前行计数器
        $exceptionCode = '';
        $currentLine = 0;

        // 打开文件
        $file = fopen($filePath, 'r');
        if ($file === false) {
            return false;
        }
        $errLine = $e->getLine();

        // 循环读取每一行直到到达指定行范围或文件结束
        while (($line = fgets($file)) !== false) {
            $currentLine++;
            if ($currentLine >= $startLine && $currentLine <= $endLine) {
                // 去除行尾的换行符，并将该行添加到结果数组中
                if ($currentLine == $errLine) {
                    $exceptionCode .= '<span class="error-line-code" style="color: red;">'.$currentLine.'|'.$line.'</span>';
                } else {
                    $exceptionCode .= $currentLine.'|'.$line;
                }
            }
            if ($currentLine > $endLine) {
                break; // 如果已经超过了所需的最后一行，则停止读取
            }
        }

        // 关闭文件
        fclose($file);

        // 返回结果数组
        return $exceptionCode;
    }
}
