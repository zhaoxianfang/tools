<?php

namespace zxf\Laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use zxf\Laravel\Trace\Handle;
use zxf\Laravel\Trace\Traits\ExceptionCodeTrait;
use zxf\Laravel\Trace\Traits\ExceptionShowDebugHtmlTrait;

/**
 * 安全拦截中间件
 *
 * 功能：
 * 1. 全面拦截各类恶意请求（XSS、SQL注入、CSRF、目录遍历等）
 * 2. 智能识别可疑行为（高频访问、异常参数、危险文件等）
 * 3. 精细化访问控制（速率限制、时段限制）
 * 4. 完善的安全事件响应（日志、邮件、封禁）
 */
class SecurityMiddleware
{
    use ExceptionCodeTrait,ExceptionShowDebugHtmlTrait;

    /**
     * 错误信息列表，用于记录拦截时的详细错误信息
     */
    protected array $errorList = [];

    /**
     * 错误代码，用于标识具体的错误类型
     */
    protected string $errorCode = '';

    // ==================== 安全模式定义 ====================

    /**
     * 对body进行恶意请求检测模式（正则表达式）
     * 包含XSS、SQL注入、命令注入等常见攻击模式
     */
    protected const MALICIOUS_BODY_PATTERNS = [
        // XSS攻击
        '/<script\b[^>]*>(.*?)<\/script>/is',          // 基本脚本标签
        '/javascript\s*:/i',                           // JavaScript伪协议
        '/on\w+\s*=\s*["\'].*?["\']/i',                // 事件处理器
        '/(data|vbscript):/i',                         // 其他危险协议
        // '/(%3C|<).*script.*(%3E|>)/i',                 // XSS

        // SQL注入
        '/\b(union\s+select|select\s+\*.*from)\b/is',  // 联合查询
        '/\b(insert\s+into|update\s+\w+\s+set)\b/is',  // 数据修改
        '/\b(drop\s+table|truncate\s+table)\b/is',     // 表删除
        '/insert\s+into/i',                            // SQL 插入
        '/\b(exec\s*\(|execute\s*\(|sp_executesql)/i', // SQL执行
        '/(or\s+\d=\d|and\s+\d=\d)/i',                 // SQL 注入
        '/delete\s+from/i',                            // SQL 删除
        // '/(benchmark\(|sleep\(|load_file\(|xp_cmdshell)/i', // SQL 时间盲注与命令注入
        '/(?:\'|"|%22|%27).*(or|and).*(=|>|<|>=|<=)/i', // 匹配更复杂的 SQL 注入模式

        // 命令注入
        '/\b(system|exec|shell_exec|passthru)\s*\(/i', // 系统命令执行
        '/`.*`/',                                      // 反引号命令执行
        // '/\|\s*\w+/',                                  // 管道符号
        // '/\&\s*\w+/',                                  // 后台执行

        // 文件操作
        '/\.\.\//',                                    // 目录遍历
        '/(\.\.\/|\.\.\\\\)/',                         // 目录遍历
        '/\b(file_get_contents|fopen|fwrite)\s*\(/i',  // 文件操作
        '/php\s*:\/\/filter/i',                       // PHP过滤器
        // '/\b(load_file|outfile|dumpfile)\b/i',              // 文件操作

        // 已知漏洞文件
        '/phpmyadmin/',              // 匹配 phpMyAdmin 目录
        '/adminer\.php$/',           // 匹配 adminer.php 数据库管理文件
        '/setup\.php$/',             // 匹配 setup.php 文件
        '/install\.php$/',           // 匹配 install.php 文件
        '/upgrade\.php$/',           // 匹配 upgrade.php 文件
        '/info\.php$/',              // 匹配 info.php 文件，可能包含 phpinfo 信息
        // '/test\.(php|html|js|asp)$/', // 匹配测试文件

        // 其他危险模式
        '/<\?php/i',                                   // PHP代码
        '/\%00/',                                      // NULL字节
        '/\b(eval|assert)\s*\(/i',                     // 动态代码执行
        '/\$(_(GET|POST|REQUEST|COOKIE|SERVER))\b/i',  // 超全局变量直接使用
        '/\b(eval|assert|passthru|popen|proc_open|pcntl_exec)\b/i', // PHP 命令执行
        '/(base64_encode\(|system\(|exec\(|shell_exec\()/i', // 代码注入
    ];

    /**
     * 非法URL路径模式
     * 用于检测试图访问敏感文件的请求
     */
    protected const ILLEGAL_URL_PATTERNS = [
        // 配置文件
        '~/(\.+[^/]*)(?=/|$)~',      // 匹配所有点(.)开头的文件或文件夹
        '/\.config(\.php)?$|'.          // 匹配 .config 和 .config.php 文件
        'composer\.(json|lock)$|'.      // 匹配 composer.json 或 composer.lock 文件
        'package\.json$/',              // 匹配 package.json 文件

        '/\.('.
        'php|jsp|asp|aspx|pl|py|rb|sh|cgi|cfm|bash|c|cpp|java|cfm|sql|yaml|yml|'. // 源代码文件
        'sql|db|db3|mdb|accdb|sqlite|sqlite3|dbf|'. // 数据库文件
        'bak|old|save|backup|orig|temp|tmp|sdk|debug|sample|secret|private|log'. // 备份和日志文件
        ')$/i',

        // 系统文件
        '/^(readme|license|changelog)\.(md|txt)$/i',   // 说明文件

        // 压缩和归档文件
        // '/\.(zip|rar|tar|gz|7z)$/',  // 匹配常见压缩文件格式

        // 敏感目录
        // '/(config|setup|install|backup|log|node_modules|vendor)/i', // 敏感目录
        '/(backup|node_modules|vendor)/i', // 敏感目录
        // 通用敏感路径
        // '/uploads/',                 // 匹配上传目录，可能需要额外保护
    ];

    /**
     * 禁止上传的文件扩展名
     * 包含可执行文件、脚本文件等危险类型
     */
    protected const DISALLOWED_EXTENSIONS = [
        // 可执行文件
        'exe', 'bat', 'cmd', 'com', 'msi', 'dll', 'so', 'bin', 'run',

        // 脚本文件
        'php', 'phtml', 'java', 'elf', 'out',
        'jsp', 'jspx', 'asp', 'aspx', 'pl', 'py', 'rb',
        'sh', 'bash', 'csh', 'ksh', 'zsh', 'cgi',

        // 配置文件
        'env', 'ini', 'conf', 'cfg', 'config', 'yml', 'yaml',

        // 其他危险文件
        'js', 'html', 'htm', 'svg', 'swf', 'jar', 'war',
        'reg', 'vbs', 'wsf', 'ps1', 'psm1', 'psd1',
    ];

    /**
     * 可疑User-Agent模式
     * 用于识别恶意爬虫和扫描工具
     */
    protected const SUSPICIOUS_USER_AGENTS = [
        '/('.
        'sqlmap|'.       // SQL注入工具
        'nikto|'.        // 漏洞扫描器
        'metasploit|'.   // 渗透测试框架
        'nessus|'.       // 漏洞扫描器
        'wpscan|'.       // WordPress扫描器
        'acunetix|'.     // Web漏洞扫描器
        'burp|'.         // 渗透测试工具
        'dirbuster|'.    // 目录爆破工具
        'hydra|'.        // 暴力破解工具
        'havij|'.        // SQL注入工具
        'zap|'.          // OWASP ZAP代理
        'arachni|'.      // Web应用扫描器
        'nmap|'.         // 端口扫描工具
        'netsparker|'.   // Web漏洞扫描器
        'w3af|'.         // Web应用攻击框架
        'fimap|'.        // 文件包含工具
        'skipfish|'.     // Web应用扫描器
        'webshag|'.      // 多线程扫描器
        'webinspect|'.   // Web应用扫描器
        'paros|'.        // Web代理扫描器
        'appscan|'.      // IBM安全扫描器
        'webscarab|'.    // OWASP WebScarab
        'beef'.          // 浏览器攻击框架
        ')/i',           // 不区分大小写匹配

        // '/curl/i',         // 可疑的curl请求
        // '/wget/i',         // 可疑的wget请求
        // '/libwww-perl/i',  // Perl LWP请求
        // '/winhttp/i',      // Windows HTTP请求
        // '/python-urllib/i', // Python URL库
    ];

    // ==================== 主处理方法 ====================

    /**
     * 处理传入的HTTP请求
     *
     * @param  Request  $request  当前HTTP请求对象
     * @param  Closure  $next  下一个中间件闭包
     * @param  string|null  $encodedConfig  传入进来的配置字符串(已经经过 base64_encode(json_encode($config)) 处理过)
     * @return mixed 返回响应或继续处理
     */
    public function handle(Request $request, Closure $next, ?string $encodedConfig = null)
    {
        // 预处理参数
        $this->handleSecurityParams($request, $encodedConfig);

        // 1、检查请求方式
        $this->checkRequestMethod($request);

        // 2. 预处理检查：爬虫和IP黑名单
        $this->preCheckSecurity($request);

        // 3. 获取请求基本信息
        $ip = $this->getClientRealIp($request);

        // 4. 深度安全检测
        $securityCheckResult = $this->performDeepSecurityCheck($request, $ip);
        if ($securityCheckResult['block']) {
            return $this->handleSecurityViolation(
                $request,
                $securityCheckResult['type'],
                $securityCheckResult['title'],
                $securityCheckResult['message'],
                $securityCheckResult['context']
            );
        }

        // 5. 检查黑名单
        $blackInfo = $this->checkBlacklist($request, $ip);
        if ($blackInfo['block']) {
            return $blackInfo['response'];
        }

        // 6. 开发者自定义处理逻辑
        $this->handleCustomLogic($request);

        // 7. 请求正常，继续处理
        return $next($request);
    }

    // 检查请求方式
    protected function checkRequestMethod(Request $request): void
    {
        $allowMethods = $this->getMiddlewareConfig($request, 'allow_methods');
        if (! empty($allowMethods)) {
            if (! in_array($request->method(), $allowMethods)) {
                $this->handleSecurityViolation(
                    $request,
                    '请求方式拦截',
                    '请求方式拦截',
                    '不被允许的请求方式:'.$request->method()
                );
            }
        }
    }

    // 开发者自定义处理逻辑
    protected function handleCustomLogic(Request $request): void
    {
        try {
            $customHandle = $this->getMiddlewareConfig($request, 'custom_handle');
            if (empty($customHandle)) {
                return;
            }
            $call_class = $this->getFuncClass($customHandle);
            // 要求返回结构 [
            // "type"=>"拦截类型",
            // "title"=>"拦截标题",
            // "message"=>"拦截提示信息",
            // "context"=>"拦截附加信息",
            // ]
            $res = call_user_func_array($call_class, [$request]);
            if (! empty($res) && is_array($res) && ! empty($res['message'])) {
                $this->handleSecurityViolation(
                    $request,
                    ! empty($res['type']) ? $res['type'] : '请求拦截',
                    ! empty($res['title']) ? $res['title'] : '操作拦截',
                    $res['message'],
                    ! empty($res['context']) ? $res['context'] : [],
                );
            }
        } catch (\Exception $e) {
        }
    }

    // ==================== 参数处理方法 ====================
    protected function handleSecurityParams(Request $request, ?string $encodedConfig = null): void
    {
        if (! empty($encodedConfig)) {
            try {
                $config = json_decode(base64_decode($encodedConfig), true, 512, JSON_THROW_ON_ERROR);
                // 将配置存入请求对象以便后续使用
                // $request->attributes->set('security_middleware_config', $config);
                $request->merge(['security_middleware_config' => $config]);
            } catch (\Exception $e) {
            }
        }
    }

    // ==================== 安全检测方法 ====================

    /**
     * 预处理安全检查
     * 包括爬虫检查和IP黑名单检查
     *
     * @param  Request  $request  当前请求对象
     */
    protected function preCheckSecurity(Request $request): void
    {
        $userAgent = $request->userAgent();

        // 1. 检查禁止的爬虫
        $this->checkBannedSpiders($request, $userAgent);

        // 2. 检查可疑User-Agent
        $this->checkSuspiciousUserAgent($request, $userAgent);
    }

    /**
     * 执行深度安全检测
     * 包括可疑请求和恶意请求检测
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $ip  客户端IP
     * @return array 检测结果
     */
    protected function performDeepSecurityCheck(Request $request, string $ip): array
    {
        // 1. 检查可疑请求（危险文件上传、可疑URL等）
        if ($this->isSuspiciousRequest($request)) {
            return [
                'block' => true,
                'type' => 'Suspicious',
                'title' => '可疑请求拦截',
                'message' => '系统检测到可疑操作',
                'context' => ['errors' => $this->errorList],
            ];
        }

        // 2. 检查恶意请求（XSS、SQL注入等）
        if ($this->isMaliciousRequest($request)) {
            return [
                'block' => true,
                'type' => 'Malicious',
                'title' => '恶意请求拦截',
                'message' => '系统检测到潜在攻击行为',
                'context' => [
                    'error_code' => $this->errorCode,
                    'pattern' => $this->errorCode,
                ],
            ];
        }

        // 3. 检查异常参数（非常规参数组合）
        // if ($this->hasAnomalousParameters($request)) {
        //     return [
        //         'block' => true,
        //         'type' => 'Anomalous',
        //         'title' => '异常参数拦截',
        //         'message' => '请求包含异常参数组合',
        //         'context' => ['params' => $request->all()],
        //     ];
        // }

        // 4. 检查请求指纹（识别自动化工具）
        // if ($this->hasSuspiciousFingerprint($request)) {
        //     return [
        //         'block' => true,
        //         'type' => 'Automation',
        //         'title' => '自动化工具拦截',
        //         'message' => '检测到自动化工具访问',
        //         'context' => ['fingerprint' => $this->getRequestFingerprint($request)],
        //     ];
        // }

        return ['block' => false];
    }

    // ==================== 具体检测方法 ====================

    /**
     * 检查禁止的爬虫
     *
     * @param  Request  $request  当前请求对象
     */
    protected function checkBannedSpiders(Request $request, ?string $userAgent = null): void
    {
        if (empty($userAgent)) {
            $this->handleSecurityViolation(
                $request,
                'EmptyUA',
                '空User-Agent拦截',
                '请求未包含User-Agent头',
                ['ip' => $request->ip()]
            );
        }

        // 2. database 检查禁止的爬虫
    }

    /**
     * 检查可疑User-Agent
     *
     * @param  Request  $request  当前请求对象
     */
    protected function checkSuspiciousUserAgent(Request $request, ?string $userAgent = null): void
    {
        if (empty($userAgent)) {
            return;
        }

        // 不允许包含的 User-Agent
        $banUserAgent = $this->getMiddlewareConfig($request, 'forbid_user_agent');

        $banUserAgent = is_array($banUserAgent) && ! empty($banUserAgent) ? $banUserAgent : self::SUSPICIOUS_USER_AGENTS;
        if (empty($banUserAgent)) {
            return;
        }

        foreach ($banUserAgent as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $this->handleSecurityViolation(
                    $request,
                    'SuspiciousUA',
                    '可疑工具拦截',
                    '检测到安全扫描工具',
                    ['ua' => $userAgent, 'pattern' => $pattern]
                );
            }
        }
    }

    /**
     * 检查可疑请求
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否可疑
     */
    protected function isSuspiciousRequest(Request $request): bool
    {
        // 1. 检查危险文件上传
        if ($this->hasDangerousUploads($request)) {
            return true;
        }

        // 2. 检查危险URL
        if (! $this->isSafeUrl($request, urldecode($request->fullUrl()))) {
            return true;
        }

        // 3. 检查异常HTTP头
        if ($this->hasSuspiciousHeaders($request)) {
            return true;
        }

        // 4. 检查异常HTTP方法
        if ($this->hasSuspiciousMethod($request)) {
            return true;
        }

        return false;
    }

    /**
     * 检查恶意请求
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否恶意
     */
    protected function isMaliciousRequest(Request $request): bool
    {
        $input = $request->input(); // 只对请求body参数 进行检查

        // 获取uri
        $path = $request->path(); // 仅路径

        // 不进行验证的白名单地址
        $whitelistPath = $this->getMiddlewareConfig($request, 'whitelist_path_of_not_verify_body');
        if (is_array($whitelistPath) && ! empty($whitelistPath)) {
            if (in_array($path, $whitelistPath)) {
                return false;
            }
        }

        $bodyRegExp = $this->getMiddlewareConfig($request, 'reg_exp_body');

        $regExp = is_array($bodyRegExp) && ! empty($bodyRegExp) ? $bodyRegExp : self::MALICIOUS_BODY_PATTERNS;
        if (empty($regExp)) {
            return false;
        }
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            // 移除不可见字符（保留常规空格）
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

            if (is_string($value) && ! $this->checkIsHtml($value)) {
                // 判断提交内容是否为 markdown
                if ($this->checkIsMarkdown($value)) {
                    // 1. 移除代码块内容（代码块中的内容不进行安全检测）
                    $value = $this->pruneMarkdownCode($value);
                } else {
                    // 2. 移除 HTML 标签
                    $value = detach_html($value);
                }

                // 3. 对剩余内容进行正则安全检测
                foreach ($regExp as $pattern) {
                    // 排除某些特殊情况（如文章内容）
                    if (preg_match($pattern, $value) && ! $this->isFalsePositive($request, $key, $value)) {
                        $this->errorCode = substr($value, 0, 100);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 黑名单ip 处理
     *
     * @return array|false[]
     */
    public function checkBlacklist(Request $request, string $ip)
    {
        if ($this->isLocalIp($ip)) {
            return [
                'block' => false,
            ];
        }
        try {
            $customBlacklistHandle = $this->getMiddlewareConfig($request, 'blacklist_handle');
            if (empty($customBlacklistHandle)) {
                return ['block' => false];
            }
            $call_class = $this->getFuncClass($customBlacklistHandle);
            // 要求返回结构 [bool(是否拦截),'拦截信息']
            $res = call_user_func_array($call_class, [$ip]);
            if (! empty($res) && is_array($res) && $res[0] === true && ! empty($res[1])) {
                return [
                    'block' => true,
                    'response' => $this->handleSecurityViolation(
                        $request,
                        'Blacklist',
                        '黑名单/Ip拦截',
                        $res[1],
                        ['ip' => $ip],
                    ),
                ];
            }
        } catch (\Exception $e) {
        }

        return ['block' => false];
    }

    /**
     * 获取中间件配置项
     */
    protected function getMiddlewareConfig(Request $request, string $name): mixed
    {
        if (! $request->has('security_middleware_config')) {
            return null;
        }
        $config = $request->input('security_middleware_config');

        return $config[$name] ?? null;
    }

    /**
     * 去除 $string 两边的空格、单引号和双引号
     */
    private function enhancedTrim(string $string): string
    {
        // 去除字符串两边的空格和引号
        $trimmed = trim($string, " \t\n\r\0\x0B'\"");

        // 使用正则表达式移除开头和结尾的多重引号（嵌套引号情况）
        $trimmed = preg_replace('/^(["\']+)(.*?)(\1)+$/', '$2', $trimmed);

        // 再次清理两边空格（确保清理完正则后的残余空格）
        return trim($trimmed);
    }

    /**
     * 获取 func 执行类型 的执行对象类
     * 静态方法(字符串) \Modules\Test\Services\TestService::init   或
     * 普通方法(字符串) ['\Modules\Test\Services\TestService','test']
     * 或PHP8+类(数组) [\Modules\Test\Services\TestService::class,'test']
     */
    private function getFuncClass(string|array $classOrFunc = ''): array|string
    {
        if (is_array($classOrFunc)) {
            if (count($classOrFunc) == 2) {
                return [App::make($classOrFunc[0]), $classOrFunc[1]];
            }

            return [];
        }
        // 判断 $classOrFunc 里面是否包含逗号
        // 判断 $classOrFunc 是否是 [ 开头且 ]结尾
        if (str_contains($classOrFunc, ',') && preg_match('/^\[(.*)\]$/', $classOrFunc, $matches)) {
            $class_or_func = $matches[1];
            // 使用,分割$class_or_func，第一个参数是类名，第二个参数是方法名
            [$class, $method] = explode(',', $class_or_func);
            // 去除 $class 和 $method 两边的空格、单引号和双引号
            $class = $this->enhancedTrim($class);
            $method = $this->enhancedTrim($method);

            return [App::make(trim($class)), trim($method)];
        } else {
            return $this->enhancedTrim($classOrFunc);
        }
    }

    /**
     * Markdown检测函数
     * 要求必须包含#标题 AND (```代码块 OR `行内代码`)
     *
     * @param  string|null  $content  要检查的内容
     * @return bool 如果是符合严格条件的Markdown返回true
     */
    public function checkIsMarkdown(?string $content = ''): bool
    {
        $trimmed = trim($content);
        if (empty($trimmed)) {
            return false;
        }

        // 1. 检测是否是Markdown（必须同时包含标题和代码）
        return preg_match('/^#{1,6}\s+\w+/m', $trimmed) && preg_match('/(^```[a-z]*\s*[\s\S]+?^```$|`[^`]+`)/m', $trimmed);
    }

    /**
     * 判断字符串是否为HTML格式
     *
     * @param  string|null  $content  要检查的内容
     */
    private function checkIsHtml(?string $content = ''): bool
    {
        $content = trim($content);

        // 快速检查：空内容或缺少基本HTML特征
        if (empty($content) ||
            ! preg_match('/<[a-z][a-z0-9]*[\/\s>]/i', $content)) {
            return false;
        }

        // 检查是否已经是完整HTML文档
        $isFullDocument = preg_match('/^\s*<!DOCTYPE\s+html/i', $content) ||
                          preg_match('/^\s*<html[^>]*>/i', $content);

        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);

        // 对于HTML片段，包装成完整文档
        $htmlToLoad = $isFullDocument ? $content : sprintf(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>%s</body></html>',
            $content
        );

        // 尝试加载HTML
        $loaded = @$doc->loadHTML($htmlToLoad, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // 检查是否有严重错误
        $hasCriticalErrors = false;
        foreach (libxml_get_errors() as $error) {
            // 忽略无害的HTML警告(如HTML5标签在旧规范中的警告)
            if ($error->level >= LIBXML_ERR_ERROR && ! in_array($error->code, [801, 800])) {
                $hasCriticalErrors = true;
                break;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $loaded && ! $hasCriticalErrors;
    }

    /**
     * 删除Markdown中的代码块和行内代码
     */
    public function pruneMarkdownCode(string $content): string
    {
        // 1. 移除所有Markdown格式内容
        $content = preg_replace([
            '/```[\s\S]*?```/',       // 代码块
            '/~~~[\s\S]*?~~~/',       // 替代代码块
            '/`[^`]+`/',             // 行内代码
            '/\[.*?\]\(.*?\)/',      // 链接
            '/\*\*.*?\*\*/',         // 加粗
            '/\*.*?\*/',             // 斜体
            '/<!--[\s\S]*?-->/',     // HTML注释
            '/\/\*[\s\S]*?\*\//',   // CSS/JS注释
            '/\/\/.*?(\n|$)/',        // 单行注释
        ], '', $content);

        // 清理多余空行（保留最多两个连续换行）
        // $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return trim($content);
    }

    /**
     * 检查异常参数组合
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否存在异常参数
     */
    protected function hasAnomalousParameters(Request $request): bool
    {
        // 1. 检查参数名中的可疑关键词
        // 2. 检查参数值的异常长度或编码

        return false;
    }

    /**
     * 检查请求指纹是否可疑
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否可疑
     */
    protected function hasSuspiciousFingerprint(Request $request): bool
    {
        // 1. 缺少常见头
        // 2. 异常的头顺序
        // 3. 自动化工具特征

        return false;
    }

    // ==================== 辅助方法 ====================

    /**
     * 获取客户端真实IP（考虑代理情况）
     *
     * @param  Request  $request  当前请求对象
     * @return string 客户端IP
     */
    protected function getClientRealIp(Request $request): string
    {
        $ip = $request->ip();

        // 如果使用了代理，检查X-Forwarded-For头
        if ($request->headers->has('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            $ip = trim($ips[0]);
        }

        // 验证IP格式
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * 检查是否有危险文件上传
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否存在危险文件
     */
    protected function hasDangerousUploads(Request $request): bool
    {
        foreach ($request->allFiles() as $file) {
            if (! $this->isSafeFile($request, $file)) {
                $this->errorList['dangerous_file'] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];

                return true;
            }
        }

        return false;
    }

    /**
     * 检查文件是否安全
     *
     * @param  mixed  $file  文件对象
     * @return bool 是否安全
     */
    protected function isSafeFile(Request $request, $file): bool
    {
        if (! $file instanceof UploadedFile) {
            return true;
        }
        // 禁止上传的文件扩展名后缀
        $banFileExt = $this->getMiddlewareConfig($request, 'forbid_upload_file_ext');

        $banExp = is_array($banFileExt) && ! empty($banFileExt) ? $banFileExt : self::DISALLOWED_EXTENSIONS;
        if (empty($banExp)) {
            return true;
        }

        // 检查扩展名
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $banExp)) {
            return false;
        }

        return true;
    }

    /**
     * 检查URL是否安全
     *
     * @param  string  $url  完整URL
     * @return bool 是否安全
     */
    protected function isSafeUrl(Request $request, string $url): bool
    {
        // 解码URL编码的字符
        $url = urldecode($url);
        $urlRegExp = $this->getMiddlewareConfig($request, 'reg_exp_url');

        $regExp = is_array($urlRegExp) && ! empty($urlRegExp) ? $urlRegExp : self::ILLEGAL_URL_PATTERNS;

        if (empty($regExp)) {
            return true;
        }

        // 检查非法URL模式
        foreach ($regExp as $pattern) {
            try {
                if (preg_match($pattern, $url)) {
                    return false;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return true;
    }

    /**
     * 检查是否有可疑HTTP头
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否存在可疑头
     */
    protected function hasSuspiciousHeaders(Request $request): bool
    {
        return false;
    }

    /**
     * 检查是否有可疑HTTP方法
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否可疑
     */
    protected function hasSuspiciousMethod(Request $request): bool
    {
        $normalMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $method = strtoupper($request->method());

        // 非常规方法
        if (! in_array($method, $normalMethods)) {
            $this->errorList['suspicious_method'] = $method;

            return true;
        }

        return false;
    }

    /**
     * 检查是否为误报（某些合法请求可能包含类似攻击的模式）
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $key  参数名
     * @param  string  $value  参数值
     * @return bool 是否误报
     */
    protected function isFalsePositive(Request $request, string $key, string $value): bool
    {
        // 排除某些特殊路由
        // $excludedRoutes = ['api/comments', 'api/posts', 'api/articles'];
        // if (in_array($request->path(), $excludedRoutes)) {
        //     return true;
        // }

        // 排除某些参数名
        $excludedKeys = ['content', 'body', 'description', 'markdown'];
        if (in_array($key, $excludedKeys)) {
            return true;
        }

        // 排除某些内容类型
        // $excludedContentTypes = ['application/json', 'text/markdown'];

        return false;
    }

    /**
     * 获取请求指纹（用于识别请求唯一性）
     *
     * @param  Request  $request  当前请求对象
     * @return string 指纹哈希
     */
    protected function getRequestFingerprint(Request $request): string
    {
        $data = [
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'path' => $request->path(),
            'method' => $request->method(),
        ];

        return md5(json_encode($data));
    }

    // ==================== 响应处理方法 ====================

    /**
     * 处理安全违规请求
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $type  违规类型
     * @param  string  $title  标题
     * @param  string  $message  消息
     * @param  array  $context  上下文信息
     * @return mixed 拦截响应
     */
    protected function handleSecurityViolation(
        Request $request,
        string $type,
        string $title,
        string $message,
        array $context = []
    ) {
        // 记录安全事件
        $this->logSecurityEvent($request, $type, $title, $context);

        // 发送安全警报（如果需要）
        if ($this->shouldSendAlert($type)) {
            try {
                $this->sendSecurityAlert($request, $type, $title, $context);
            } catch (\Exception $e) {
            }
        }

        // 返回适当的响应
        return $this->createSecurityResponse($request, $type, $title, $message, $context);
    }

    /**
     * 记录安全事件
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $type  事件类型
     * @param  string  $title  事件标题
     * @param  array  $context  上下文信息
     */
    protected function logSecurityEvent(
        Request $request,
        string $type,
        string $title,
        array $context = []
    ): void {
        $logData = array_merge([
            'type' => $type,
            'title' => $title,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ], $context);

        // 标记日志已经被记录过了，防止重复记录；其他地方可以通过 $request->has('log_already_recorded') 来判断
        $request->merge(['log_already_recorded' => true]);

        // 使用独立的安全日志通道
        Log::error("安全拦截: {$title}", $logData);
    }

    /**
     * 判断是否应该封禁
     *
     * @param  string  $type  违规类型
     * @return bool 是否封禁
     */
    protected function shouldBan(string $type): bool
    {
        return in_array($type, ['Malicious', 'Anomalous', 'RateLimit']);
    }

    /**
     * 获取封禁时长（小时）
     *
     * @param  string  $type  违规类型
     * @param  int  $banNumber  封禁次数
     * @return int 封禁时长
     */
    protected function getBanDuration(string $type, int $banNumber): int
    {
        $baseDuration = match ($type) {
            'Malicious' => 24,
            'Anomalous' => 12,
            'RateLimit' => 6,
            default => 1
        };

        // 封禁时长随次数递增，但有上限
        return min($baseDuration * $banNumber, 720); // 最多30天
    }

    /**
     * 判断是否应该发送警报
     *
     * @param  string  $type  违规类型
     * @return bool 是否发送
     */
    protected function shouldSendAlert(string $type): bool
    {
        return in_array($type, ['Malicious', 'Anomalous']);
    }

    /**
     * 发送安全警报
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $type  警报类型
     * @param  string  $title  警报标题
     * @param  array  $context  上下文信息
     */
    protected function sendSecurityAlert(Request $request, string $type, string $title, array $context): void
    {
        try {
            $data = [
                'title' => "安全警报: {$title}",
                'type' => $type,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'time' => now()->toDateTimeString(),
                'context' => $context,
            ];

            $securityAlarmHandle = $this->getMiddlewareConfig($request, 'send_security_alarm_handle');
            if (empty($securityAlarmHandle)) {
                return;
            }
            $call_class = $this->getFuncClass($securityAlarmHandle);
            // 不需要返回数据
            call_user_func_array($call_class, [$data]);
            Log::warning('已经发送安全警报', $data);
        } catch (\Exception $e) {
            Log::error("发送安全警报失败: {$e->getMessage()}");
        }
    }

    /**
     * 创建安全响应
     *
     * @param  Request  $request  当前请求对象
     * @param  string  $type  响应类型
     * @param  string  $title  标题
     * @param  string  $message  消息
     * @return mixed HTTP响应
     */
    protected function createSecurityResponse(Request $request, string $type, string $title, string $message, ?array $context = [])
    {
        $statusCode = $this->getStatusCode($type);

        $responseData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'status' => $statusCode,
            'context' => $context,
        ];

        // API请求返回JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            $respFormat = [
                'code' => 'code',
                'message' => 'message',
                'data' => 'data',
            ];
            $ajaxRespFormat = $this->getMiddlewareConfig($request, 'ajax_resp_format');
            if (! empty($ajaxRespFormat)) {
                $respFormat = array_merge($respFormat, $ajaxRespFormat);
            }
            $response = [
                $respFormat['code'] => $statusCode,
                $respFormat['message'] => $message,
                $respFormat['data'] => [
                    'title' => $title,
                    'type' => $type,
                ],
            ];

            return response()->json($response, $statusCode);
        }

        if (config('app.debug')) {
            return $this->outputDebugHtml($responseData, '操作异常拦截', $statusCode);
        }

        // Web请求返回视图
        $resp = $this->respView('[异常拦截]'.$message, $statusCode);

        // 默认纯文本响应
        // $resp = response('<h3>'.$statusCode.':'.$message.'</h3>', $statusCode);

        /** @var Handle $trace */
        $trace = app('trace');

        return $trace->renderTraceStyleAndScript(request(), $resp)->send();
    }

    /**
     * 获取HTTP状态码
     *
     * @param  string  $type  拦截类型
     * @return int HTTP状态码
     */
    protected function getStatusCode(string $type): int
    {
        return match ($type) {
            'Forbidden' => 403,
            'Malicious' => 403,
            'Anomalous' => 400,
            'RateLimit' => 429,
            'Suspicious' => 422,
            default => 403
        };
    }

    /**
     * 判断是否为本地IP
     *
     * @param  string  $ip  IP地址
     * @return bool 是否本地IP
     */
    protected function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false;
    }
}
