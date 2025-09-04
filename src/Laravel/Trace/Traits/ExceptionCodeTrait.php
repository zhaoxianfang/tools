<?php

namespace zxf\Laravel\Trace\Traits;

/**
 * 异常处理状态码 Trait
 */
trait ExceptionCodeTrait
{
    // 错误码
    public static array $codeMap = [
        0 => '请求出错',

        // 1xx Information (信息响应)
        100 => '继续', // Continue (继续); 服务器已收到请求头，客户端应继续发送请求体
        101 => '切换协议', // Switching Protocols (切换协议); 服务器根据客户端请求切换协议
        102 => '处理中', // Processing (处理中); 服务器已接受并正在处理请求，但无响应可用
        103 => '早期提示', // Early Hints (早期提示); 用于在最终HTTP消息之前返回一些响应头

        // 2xx Success (成功响应)
        200 => '请求成功', // OK (成功); 请求成功。一般用于GET与POST请求
        201 => '资源已创建', // Created (已创建); 请求成功并且服务器创建了新的资源
        202 => '请求已接受', // Accepted (已接受); 服务器已接受请求，但尚未处理完成
        203 => '非授权信息', // Non-Authoritative Information (非授权信息); 请求成功，但返回的元信息不在原始服务器上
        204 => '无内容', // No Content (无内容); 服务器成功处理请求，但未返回任何内容
        205 => '重置内容', // Reset Content (重置内容); 服务器处理成功，要求客户端重置文档视图
        206 => '部分内容', // Partial Content (部分内容); 服务器成功处理了部分GET请求
        207 => '多状态', // Multi-Status (多状态); 消息体将是一个XML消息，包含多个独立的响应代码
        208 => '已报告', // Already Reported (已报告); DAV绑定的成员已经在(多状态)响应之前部分列举
        226 => 'IM已使用', // IM Used (IM已使用); 服务器已完成对资源的GET请求，响应是当前实例应用的一个或多个实例操作的结果

        // 3xx Redirection (重定向)
        300 => '多种选择', // Multiple Choices (多种选择); 请求的资源有多种选择
        301 => '永久重定向', // Moved Permanently (永久移动); 请求的资源已被永久的移动到新URI
        302 => '临时重定向', // Found (临时移动); 请求的资源临时从不同的URI响应请求
        303 => '查看其他地址', // See Other (查看其他地址); 对应当前请求的响应可以在另一个URI上被找到
        304 => '未修改', // Not Modified (未修改); 所请求的资源未修改，服务器返回此状态码时不会返回任何资源
        305 => '使用代理', // Use Proxy (使用代理); 所请求的资源必须通过代理访问
        306 => '切换代理', // Switch Proxy (切换代理); 在最新规范中，该状态码已废弃
        307 => '临时重定向', // Temporary Redirect (临时重定向); 请求的资源临时从不同的URI响应请求，但请求方法不变
        308 => '永久重定向', // Permanent Redirect (永久重定向); 资源永久重定向到新的URI，且请求方法不变

        // 4xx Client Error (客户端错误)
        400 => '错误请求', // Bad Request (错误请求); 客户端请求的语法错误，服务器无法理解
        401 => '未授权', // Unauthorized (未授权); 请求要求用户的身份认证
        402 => '需要付款', // Payment Required (需要付款); 保留，将来使用
        403 => '禁止访问', // Forbidden (禁止); 服务器理解请求客户端的请求，但是拒绝执行此请求
        404 => '页面未找到', // Not Found (未找到); 服务器无法根据客户端的请求找到资源
        405 => '方法不被允许', // Method Not Allowed (方法禁用); 客户端请求中的方法被禁止
        406 => '无法接受', // Not Acceptable (不接受); 服务器无法根据客户端请求的内容特性完成请求
        407 => '需要代理认证', // Proxy Authentication Required (需要代理授权); 请求要求代理的身份认证
        408 => '请求超时', // Request Time-out (请求超时); 服务器等待客户端发送的请求时间过长，超时
        409 => '请求冲突', // Conflict (冲突); 服务器完成请求时发生冲突
        410 => '资源已删除', // Gone (已删除); 客户端请求的资源已经不存在
        411 => '需要长度', // Length Required (需要有效长度); 服务器无法处理客户端发送的不带Content-Length的请求信息
        412 => '未满足前提条件', // Precondition Failed (未满足前提条件); 客户端请求信息的先决条件错误
        413 => '请求实体过大', // Payload Too Large (请求实体过大); 由于请求的实体过大，服务器无法处理，因此拒绝请求
        414 => '请求URI过长', // URI Too Long (请求的URI过长); 请求的URI过长，服务器无法处理
        415 => '不支持的媒体类型', // Unsupported Media Type (不支持的媒体类型); 服务器无法处理请求附带的媒体格式
        416 => '请求范围不符', // Range Not Satisfiable (请求范围不符合要求); 客户端请求的范围无效
        417 => '未满足期望值', // Expectation Failed (未满足期望值); 服务器无法满足Expect的请求头信息
        418 => '我是茶壶', // I'm a teapot (我是茶壶); 超文本咖啡壶控制协议，是愚人节玩笑
        421 => '错误定向', // Misdirected Request (错误的请求); 请求被指向到无法生成响应的服务器
        422 => '不可处理的实体', // Unprocessable Entity (不可处理的实体); 请求格式正确，但是由于含有语义错误，无法响应
        423 => '资源已锁定', // Locked (已锁定); 当前资源被锁定
        424 => '依赖失败', // Failed Dependency (失败的依赖); 由于之前的某个请求发生的错误，导致当前请求失败
        425 => '请求太早', // Too Early (太早); 服务器不愿意冒风险来处理该请求
        426 => '需要升级', // Upgrade Required (需要升级); 客户端应当切换到TLS/1.0
        428 => '需要先决条件', // Precondition Required (需要先决条件); 原始服务器要求该请求是有条件的
        429 => '请求过多', // Too Many Requests (太多请求); 用户在给定的时间内发送了太多的请求
        431 => '请求头过大', // Request Header Fields Too Large (请求头字段太大); 服务器不愿处理请求，因为一个或多个头字段过大
        444 => '无响应', // No Response (无响应); Nginx服务器扩展，服务器不向客户端返回任何信息并关闭连接
        449 => '重试操作', // Retry With (重试操作); Microsoft扩展，客户端应重新发出请求
        451 => '法律原因不可用', // Unavailable For Legal Reasons (因法律原因不可用); 该请求因法律原因不可用

        // 5xx Server Error (服务器错误)
        500 => '服务器内部错误', // Internal Server Error (服务器内部错误); 服务器遇到错误，无法完成请求
        501 => '未实现', // Not Implemented (尚未实施); 服务器不支持请求的功能，无法完成请求
        502 => '网关错误', // Bad Gateway (错误网关); 作为网关或者代理工作的服务器尝试执行请求时，从上游服务器接收到无效的响应
        503 => '服务不可用', // Service Unavailable (服务不可用); 由于超载或系统维护，服务器暂时的无法处理客户端的请求
        504 => '网关超时', // Gateway Time-out (网关超时); 充当网关或代理的服务器，未及时从远端服务器获取请求
        505 => 'HTTP版本不支持', // HTTP Version not supported (HTTP版本不受支持); 服务器不支持请求报文使用的HTTP协议版本
        506 => '变体协商错误', // Variant Also Negotiates (变体也可协商); 服务器存在内部配置错误
        507 => '存储空间不足', // Insufficient Storage (存储空间不足); 服务器无法存储完成请求所必须的内容
        508 => '检测到循环', // Loop Detected (检测到循环); 服务器在处理请求时检测到无限循环
        510 => '未扩展', // Not Extended (未扩展); 获取资源所需要的策略并没有被满足
        511 => '需要网络认证', // Network Authentication Required (需要网络认证); 客户端需要进行身份验证才能获得网络访问权限
        599 => '网络连接超时', // Network Connect Timeout Error (网络连接超时错误); 某些代理服务器使用的非标准状态码

        // Cloudflare 和 Nginx 扩展状态码
        520 => '未知错误', // Unknown Error (未知错误); Cloudflare扩展，服务器返回了未知错误
        521 => 'Web服务器宕机', // Web Server Is Down (Web服务器已关闭); Cloudflare扩展，Web服务器已关闭
        522 => '连接超时', // Connection Timed Out (连接超时); Cloudflare扩展，连接到源服务器超时
        523 => '源站不可达', // Origin Is Unreachable (源站不可达); Cloudflare扩展，无法到达源服务器
        524 => '超时发生', // A Timeout Occurred (发生超时); Cloudflare扩展，与源服务器的连接建立成功但响应超时
        525 => 'SSL握手失败', // SSL Handshake Failed (SSL握手失败); Cloudflare扩展，与源服务器的SSL握手失败
        526 => '无效SSL证书', // Invalid SSL Certificate (无效SSL证书); Cloudflare扩展，无法验证源服务器的SSL证书
        527 => 'Railgun错误', // Railgun Error (Railgun错误); Cloudflare扩展，Railgun连接器错误
        530 => '源站IP被冻结', // Origin DNS Error (源站DNS错误); Cloudflare扩展，源站DNS错误或IP被冻结
    ];

    public function getCodeMeg(int $code)
    {
        return self::$codeMap[$code] ?? '未知错误';
    }

    /**
     * 展示错误异常给请求者( json )
     */
    public function respJson($message = '出错啦!', $code = 500)
    {
        $code = empty($code) ? 500 : $code;

        return response()->json([
            'code' => $code,
            'message' => $message,
        ], $code);
    }

    /**
     * 展示错误异常给请求者( view )
     */
    public function respView($message = '出错啦!', $code = 500)
    {
        $code = empty($code) ? 500 : $code;

        if (view()->exists("errors/{$code}")) {
            return response()->view("errors/{$code}", [
                'code' => $code,
                'message' => $message,
            ], $code);
        }

        $generalCode = substr($code, 0, 1).'xx';
        if (view()->exists("errors/{$generalCode}")) {
            return response()->view("errors/{$generalCode}", [
                'code' => $code,
                'message' => $message,
            ], $code);
        }
        if (view()->exists('errors/500')) {
            return response()->view('errors/500', [
                'code' => $code,
                'message' => $message,
            ], $code);
        }

        $html = '<title>出错啦</title><style>body{margin:0;padding:0;height:100vh;width:100vw;display:flex;justify-content:center;align-items:center;font-family:Arial,sans-serif;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);overflow:hidden}.message{color:red;font-size:20px;padding:20px;}.link{text-align:center;margin-top:20px}a{color:white;font-size:14px;text-align:center}.rect{background:linear-gradient(to left,#196aa8,#196aa8) left top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left top no-repeat,linear-gradient(to left,#196aa8,#196aa8) right top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) right top no-repeat,linear-gradient(to left,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to left,#196aa8,#196AA8) right bottom no-repeat,linear-gradient(to left,#196aa8,#196aa8) right bottom no-repeat;background-size:2px 15px,20px 2px,2px 15px,20px 2px;}</style><body><div class="message rect">'.$code.':'.$message.'<div class="link"><a href="/">返回首页</a></div></div></body>';

        return response($html, 500)->header('Content-Type', 'text/html');
    }
}
