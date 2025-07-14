<?php

namespace zxf\Laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

    /**
     * 允许的尝试次数，超过此次数将触发拦截
     */
    protected static int $allowAttemptsNum = 4;

    // ==================== 安全模式定义 ====================

    /**
     * 恶意请求检测模式（正则表达式）
     * 包含XSS、SQL注入、命令注入等常见攻击模式
     */
    protected const MALICIOUS_PATTERNS = [
        // XSS攻击
        '/<script\b[^>]*>(.*?)<\/script>/is',          // 基本脚本标签
        '/javascript\s*:/i',                           // JavaScript伪协议
        '/on\w+\s*=\s*["\'].*?["\']/i',               // 事件处理器
        '/(data|vbscript):/i',                         // 其他危险协议
        '/(%3C|<).*script.*(%3E|>)/i',                      // XSS

        // SQL注入
        '/\b(union\s+select|select\s+\*.*from)\b/is',  // 联合查询
        '/\b(insert\s+into|update\s+\w+\s+set)\b/is',  // 数据修改
        '/\b(drop\s+table|truncate\s+table)\b/is',     // 表删除
        '/insert\s+into/i',                            // SQL 插入
        '/\b(exec\s*\(|execute\s*\(|sp_executesql)/i', // SQL执行
        '/(or\s+\d=\d|and\s+\d=\d)/i',                 // SQL 注入
        '/delete\s+from/i',                            // SQL 删除
        '/(benchmark\(|sleep\(|load_file\(|xp_cmdshell)/i', // SQL 时间盲注与命令注入
        '/(?:\'|"|%22|%27).*(or|and).*(=|>|<|>=|<=)/i', // 匹配更复杂的 SQL 注入模式

        // 命令注入
        '/\b(system|exec|shell_exec|passthru)\s*\(/i', // 系统命令执行
        '/`.*`/',                                      // 反引号命令执行
        '/\|\s*\w+/',                                  // 管道符号
        '/\&\s*\w+/',                                  // 后台执行

        // 文件操作
        '/\.\.\//',                                    // 目录遍历
        '/(\.\.\/|\.\.\\\\)/',                         // 目录遍历
        '/\b(file_get_contents|fopen|fwrite)\s*\(/i',  // 文件操作
        '/php\s*:\/\/filter/i',                       // PHP过滤器
        '/\b(load_file|outfile|dumpfile)\b/i',              // 文件操作

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
        '/\.env$/i',                                   // 环境文件
        '/\.(ini|conf|cfg|config|settings)$/i',        // 各种配置文件
        '/\.config(\.php)?$/',       // 匹配 .config 和 .config.php 文件
        '/composer\.(json|lock)$/',  // 匹配 composer.json 或 composer.lock 文件
        '/package\.json$/',          // 匹配 package.json 文件
        '/\.y(a)?ml$/',              // 匹配 .yaml 和 .yml 文件

        // 源代码文件
        '/\.(php|jsp|asp|aspx|pl|py|rb|sh|cgi|cfm|bash|c|cpp|java|cfm|sql)$/i', // 脚本文件

        '/\.(js|css|html|htm|xml|json|yaml|yml)$/i',   // 可解析文件

        // 版本控制
        '/\.(git|svn|hg|bzr|cvs)/i',                  // 版本控制目录

        // 数据库文件
        '/\.(sql|db|db3|mdb|accdb|sqlite|sqlite3|dbf)$/i',        // 数据库文件

        // 备份文件
        '/\.(bak|old|save|backup|orig|temp|tmp|sdk|debug|sample|secret|private)$/i',   // 备份文件

        // 系统文件
        '/\.(htaccess|htpasswd|gitignore|gitmodules)/i', // 系统文件
        '/^(readme|license|changelog)\.(md|txt)$/i',   // 说明文件

        // 压缩和归档文件
        // '/\.(zip|rar|tar|gz|7z)$/',  // 匹配常见压缩文件格式

        // 敏感目录
        '/(config|setup|install|backup|log|node_modules|vendor)/i', // 敏感目录
        // 通用敏感路径
        '/\.idea/',                  // 匹配 IDE 配置文件夹
        // '/uploads/',                 // 匹配上传目录，可能需要额外保护

        // 备份和日志文件
        '/\.bak$/',                  // 匹配 .bak 文件（备份文件）
        // 站点爬虫规则会使用 robots.txt
        // '/\.(log|txt)$/',         // 匹配 .log 和 .txt 文件（日志文件）
        '/\.log/',                   // 匹配 .log文件
        '/\.(old|orig)$/',           // 匹配 .old 和 .orig 旧文件或备份文件
        '/\.save$/',                 // 匹配 .save 备份文件

        // 安全相关文件
        '/\.htaccess$/',             // 匹配 .htaccess 文件
        '/\.htpasswd$/',             // 匹配 .htpasswd 文件
        '/web\.config$/',            // 匹配 web.config 文件
        '/app\.config$/',            // 匹配 app.config 文件
        '/global\.asax$/',           // 匹配 global.asax 文件

        // 其他临时和系统文件
        '#/(\.[^/]+)#',              // 匹配 . 开头的文件
        '/\.tmp$/',                  // 匹配 .tmp 文件
        '/\._/',                     // 匹配 ._ 前缀的文件（macOS 临时文件）
        '/Thumbs\.db$/',             // 匹配 Thumbs.db 文件
        '/desktop\.ini$/',           // 匹配 desktop.ini 文件
        '/\$RECYCLE\.BIN/',          // 匹配 Windows 回收站目录
        '/System Volume Information/', // 匹配 Windows 系统信息目录
        '/\.(DS_Store|AppleDouble)$/', // 匹配 macOS 系统文件

    ];

    /**
     * 禁止上传的文件扩展名
     * 包含可执行文件、脚本文件等危险类型
     */
    protected const DISALLOWED_EXTENSIONS = [
        // 可执行文件
        'exe', 'bat', 'cmd', 'com', 'msi', 'dll', 'so',

        // 脚本文件
        'php', 'phtml',
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
        '/sqlmap/i',       // SQL注入工具
        '/nikto/i',        // 漏洞扫描器
        '/metasploit/i',   // 渗透测试框架
        '/nessus/i',       // 漏洞扫描器
        '/wpscan/i',       // WordPress扫描器
        '/acunetix/i',     // Web漏洞扫描器
        '/burp/i',         // 渗透测试工具
        '/dirbuster/i',    // 目录爆破工具
        '/hydra/i',        // 暴力破解工具
        '/havij/i',        // SQL注入工具
        '/zap/i',          // OWASP ZAP代理
        '/arachni/i',      // Web应用扫描器
        '/nmap/i',         // 端口扫描工具
        '/netsparker/i',   // Web漏洞扫描器
        '/w3af/i',         // Web应用攻击框架
        '/fimap/i',        // 文件包含工具
        '/skipfish/i',     // Web应用扫描器
        '/webshag/i',      // 多线程扫描器
        '/webinspect/i',   // Web应用扫描器
        '/paros/i',        // Web代理扫描器
        '/appscan/i',      // IBM安全扫描器
        '/webscarab/i',    // OWASP WebScarab
        '/beef/i',         // 浏览器攻击框架
        '/curl/i',         // 可疑的curl请求
        '/wget/i',         // 可疑的wget请求
        '/libwww-perl/i',  // Perl LWP请求
        '/winhttp/i',      // Windows HTTP请求
        '/python-urllib/i', // Python URL库
    ];

    // ==================== 主处理方法 ====================

    /**
     * 处理传入的HTTP请求
     *
     * @param  Request  $request  当前HTTP请求对象
     * @param  Closure  $next  下一个中间件闭包
     * @return mixed 返回响应或继续处理
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. 预处理检查：爬虫和IP黑名单
        $this->preCheckSecurity($request);

        // 2. 获取请求基本信息
        $ip = $this->getClientRealIp($request);
        $isLocalIp = $this->isLocalIp($ip);
        $userId = $this->getCurrentUserId();

        // 3. 深度安全检测
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

        // 6. 请求正常，继续处理
        return $next($request);
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
        // 1. 检查禁止的爬虫
        $this->checkBannedSpiders($request);

        // 2. 检查可疑User-Agent
        $this->checkSuspiciousUserAgent($request);
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
        if ($this->hasAnomalousParameters($request)) {
            return [
                'block' => true,
                'type' => 'Anomalous',
                'title' => '异常参数拦截',
                'message' => '请求包含异常参数组合',
                'context' => ['params' => $request->all()],
            ];
        }

        // 4. 检查请求指纹（识别自动化工具）
        if ($this->hasSuspiciousFingerprint($request)) {
            return [
                'block' => true,
                'type' => 'Automation',
                'title' => '自动化工具拦截',
                'message' => '检测到自动化工具访问',
                'context' => ['fingerprint' => $this->getRequestFingerprint($request)],
            ];
        }

        return ['block' => false];
    }

    // ==================== 具体检测方法 ====================

    /**
     * 检查禁止的爬虫
     *
     * @param  Request  $request  当前请求对象
     */
    protected function checkBannedSpiders(Request $request): void
    {
        $userAgent = $request->userAgent();
        if (empty($userAgent)) {
            $this->handleSecurityViolation(
                $request,
                'EmptyUA',
                '空User-Agent拦截',
                '请求未包含User-Agent头',
                ['ip' => $request->ip()]
            );
        }
    }

    /**
     * 检查可疑User-Agent
     *
     * @param  Request  $request  当前请求对象
     */
    protected function checkSuspiciousUserAgent(Request $request): void
    {
        $userAgent = $request->userAgent();
        if (empty($userAgent)) {
            return;
        }

        foreach (self::SUSPICIOUS_USER_AGENTS as $pattern) {
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
        if (! $this->isSafeUrl(urldecode($request->fullUrl()))) {
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
        // 合并所有输入数据，包括URL
        $input = array_merge(
            $request->input(),
            ['url' => urldecode($request->fullUrl())],
            // $request->headers->all()
        );

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (is_string($value)) {
                foreach (self::MALICIOUS_PATTERNS as $pattern) {

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
     * 检查异常参数组合
     *
     * @param  Request  $request  当前请求对象
     * @return bool 是否存在异常参数
     */
    protected function hasAnomalousParameters(Request $request): bool
    {
        // 1. 检查参数名中的可疑关键词
        $suspiciousKeys = ['cmd', 'exec', 'union', 'select', 'delete', 'update'];
        foreach ($request->keys() as $key) {
            foreach ($suspiciousKeys as $suspicious) {
                if (stripos($key, $suspicious) !== false) {
                    $this->errorList['suspicious_key'] = $key;

                    return true;
                }
            }
        }

        // 2. 检查参数值的异常长度或编码
        foreach ($request->all() as $value) {
            if (is_string($value)) {
                // 异常长的参数值
                if (strlen($value) > 1024) {
                    $this->errorList['long_value'] = strlen($value);

                    return true;
                }

                // 异常编码
                if (preg_match('/%[0-9a-f]{2}/i', $value) &&
                    urldecode($value) !== $value) {
                    $this->errorList['encoded_value'] = substr($value, 0, 50);

                    return true;
                }
            }
        }

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
        $commonHeaders = ['Accept', 'Accept-Language', 'Accept-Encoding'];
        foreach ($commonHeaders as $header) {
            if (! $request->headers->has($header)) {
                $this->errorList['missing_header'] = $header;

                return true;
            }
        }

        // 2. 异常的头顺序
        $headers = array_keys($request->headers->all());
        if (count($headers) < 3) {
            $this->errorList['few_headers'] = count($headers);

            return true;
        }

        // 3. 自动化工具特征
        $ua = $request->userAgent();
        if (empty($ua) || strlen($ua) < 20) {
            $this->errorList['suspicious_ua'] = $ua;

            return true;
        }

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
     * 获取当前用户ID
     *
     * @return int|null 用户ID或null
     */
    protected function getCurrentUserId(): ?int
    {
        return auth()->id();
        //        if (! auth('web')->guest()) {
        //            return auth('web')->id();
        //        }
        //
        //        if (! auth('api')->guest()) {
        //            return auth('api')->id();
        //        }
        //
        //        return null;
    }

    /**
     * 获取当前请求速率
     *
     * @param  string  $ip  客户端IP
     * @return array 速率信息
     */
    protected function getCurrentRate(string $ip): array
    {
        $key = 'rate_limit:'.$ip.':*';
        $keys = Cache::getStore()->getRedis()->keys($key);

        return [
            'count' => count($keys),
            'details' => array_slice($keys, 0, 10),
        ];
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
            if (! $this->isSafeFile($file)) {
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
    protected function isSafeFile($file): bool
    {
        if (! $file instanceof UploadedFile) {
            return true;
        }

        // 检查扩展名
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::DISALLOWED_EXTENSIONS)) {
            return false;
        }

        // 检查MIME类型
        $mime = strtolower($file->getClientMimeType());
        $dangerousMimes = [
            'application/x-php',
            'application/x-httpd-php',
            'text/x-php',
            'application/x-javascript',
            'application/x-msdownload',
        ];
        if (in_array($mime, $dangerousMimes)) {
            return false;
        }

        // 检查文件内容（前100字节）
        $content = file_get_contents($file->getRealPath(), false, null, 0, 100);
        if (preg_match('/<\?php|<\?=|script|eval\(/i', $content)) {
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
    protected function isSafeUrl(string $url): bool
    {
        // 检查非法URL模式
        foreach (self::ILLEGAL_URL_PATTERNS as $pattern) {
            try {
                if (preg_match($pattern, $url)) {
                    return false;
                }
            } catch (\Exception $e) {
                continue;
            }

        }

        // 检查异常编码
        if (preg_match('/%[0-9a-f]{2,}/i', $url) && urldecode($url) !== $url) {
            return false;
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
        $suspiciousHeaders = [
            'X-Forwarded-For' => '/^[\d\.\,\s]+$/',  // 应只包含IP和逗号
            'User-Agent' => '/.+/',                  // 不应为空
            'Accept' => '/.+\/.+/',                  // 应包含类型/子类型
            'Referer' => '/^https?:\/\/.+/',          // 应包含协议
        ];

        foreach ($suspiciousHeaders as $header => $pattern) {
            if ($request->headers->has($header) &&
                ! preg_match($pattern, $request->header($header))) {
                $this->errorList['suspicious_header'] = [
                    'header' => $header,
                    'value' => substr($request->header($header), 0, 100),
                ];

                return true;
            }
        }

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

        // GET请求但有body内容
        if ($method === 'GET' && $request->getContent()) {
            $this->errorList['get_with_body'] = strlen($request->getContent());

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
        $excludedRoutes = ['api/comments', 'api/posts', 'api/articles'];
        if (in_array($request->path(), $excludedRoutes)) {
            return true;
        }

        // 排除某些参数名
        $excludedKeys = ['content', 'body', 'description', 'markdown'];
        if (in_array($key, $excludedKeys)) {
            return true;
        }

        // 排除某些内容类型
        //        $excludedContentTypes = ['application/json', 'text/markdown'];
        //        if (in_array($request->getContentType(), $excludedContentTypes)) {
        //            return true;
        //        }

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
        // 标记请求已被拦截
        $request->merge(['security_blocked' => true]);

        // 记录安全事件
        $this->logSecurityEvent($request, $type, $title, $context);

        // 发送安全警报（如果需要）
        if ($this->shouldSendAlert($type)) {
            // $this->sendSecurityAlert($request, $type, $title, $context);
        }

        // 返回适当的响应
        return $this->createSecurityResponse($request, $type, $title, $message);
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
            'user_id' => $this->getCurrentUserId(),
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
    protected function sendSecurityAlert(
        Request $request,
        string $type,
        string $title,
        array $context
    ): void {
        try {
            $data = [
                'title' => "安全警报: {$title}",
                'type' => $type,
                'ip' => $request->ip(),
                'user_id' => $this->getCurrentUserId(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'time' => now()->toDateTimeString(),
                'context' => $context,
            ];

            // 渲染邮件内容
            // $html = view('emails.security_alert', $data)->render();

            // 发送邮件给安全团队
            // send_email(
            //     SystemEmails::$securityTeam,
            //     "安全警报: {$title}",
            //     $html,
            //     'high'
            // );
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
    protected function createSecurityResponse(
        Request $request,
        string $type,
        string $title,
        string $message
    ) {
        $statusCode = $this->getStatusCode($type);
        $responseData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'status' => $statusCode,
        ];

        // API请求返回JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData, $statusCode);
        }

        if (config('app.debug')) {
            return $this->outputDebugHtml($responseData, '操作异常拦截');
        }

        // Web请求返回视图
        if (view()->exists("errors.{$statusCode}")) {
            return response()->view(
                "errors.{$statusCode}",
                $responseData,
                $statusCode
            );
        }

        return $this->respView('[异常拦截]'.$message, $statusCode);

        // 默认纯文本响应
        // return response('<h3>'.$statusCode.':'.$message.'</h3>', $statusCode);
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
