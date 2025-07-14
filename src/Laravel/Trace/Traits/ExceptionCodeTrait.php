<?php

namespace zxf\Laravel\Trace\Traits;

/**
 * 异常处理状态码 Trait
 */
trait ExceptionCodeTrait
{
    // 错误码
    public static array $codeMap = [
        0 => '请求出错啦',
        100 => '请求已被接收，请继续发送剩余部分的请求',
        102 => '处理将被继续执行',
        200 => '请求成功',
        201 => '请求已经被实现',
        202 => '暂不处理',
        204 => '处理完成',
        205 => '处理完成',
        206 => '处理完成',
        207 => '之后的消息体将是一个XML消息',
        301 => '被请求的资源已永久移动到新位置',
        302 => '请求的资源现在临时从不同的 URI 响应请求',
        303 => '当前请求的响应已经找到',
        305 => '被请求的资源必须通过指定的代理才能被访问',
        400 => '请求参数有误',
        401 => '当前请求需要验证Authorization',
        403 => '未授权操作',
        404 => '页面不存在|资源不存在',
        405 => '请求方法有误',
        406 => '无法生成响应实体',
        408 => '请求超时',
        410 => '被请求的资源在服务器上已经不再可用',
        411 => '服务器拒绝在没有定义 Content-Length 头的情况下接受请求',
        412 => '数据验证失败',
        413 => 'URI过长',
        415 => '请求格式错误',
        421 => '从当前客户端所在的IP地址到服务器的连接数超过了服务器许可的最大范围',
        422 => '语义错误',
        424 => '之前的某个请求发生的错误，导致当前请求失败',
        426 => '客户端应当切换到TLS/1.0',
        429 => '请求太频繁了,请稍后再试',
        500 => '服务异常',
        501 => '暂不支持该功能',
        502 => '网关请求出错',
        503 => '服务器临时出错',
        504 => '网关请求出错',
        505 => '服务器不支持，或者拒绝支持在请求中使用的 HTTP 版本',
        506 => '服务器内部配置错误',
        507 => '服务器无法存储完成请求所必须的内容',
        509 => '服务器达到带宽限制',
        510 => '获取资源所需要的策略并没有没满足',
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
