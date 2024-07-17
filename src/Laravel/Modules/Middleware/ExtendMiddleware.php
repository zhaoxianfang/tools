<?php

namespace zxf\Laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use zxf\Laravel\Trace\Handle;

class ExtendMiddleware
{
    protected $handle;

    /**
     * 模块扩展中间件
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->handle = new Handle($request);

        $this->handle->listenModelEvent();

//        // ================================================
//        // 为的控制器添加周期函数 initialize 方法
//        // 说明：此周期函数在控制器中定义实现，它是在控制器的构造函数之后调用的。  但是中间件还没加载结束
//        // 演示：public function initialize(Request $request) { dump('Test initialize'); }
//        // ================================================
//
//        // 获取当前控制器
//        $controller = $request->route()->getController();
//
//        // 判断$controller存在且不是闭包函数
//        if (!empty($controller) && !$controller instanceof Closure) {
//            // 判断$controller中是否存在 initialize 方法
//            if (method_exists($controller, 'initialize')) {
//                // 直接调用 控制器中的 initialize 方法, 并传入$request参数，而不需要重新实例化控制器
//                $controller->callAction('initialize', [$request]);
//            }
//        }
//
//        // ================================================
//        // 为所有的控制器添加周期函数 initialize 方法
//        // ================================================

        $response = $next($request);

        // 在响应发送到浏览器前处理任务。
        $this->attachStyleAndScript($request, $response);

        return $response;
    }

    private function attachStyleAndScript(Request $request, Response $response): Response
    {
        $openTrace = !app()->runningInConsole() && !app()->environment('testing') && $request->isMethod('get') && config('modules.trace');

        if (!$openTrace) {
            return $response;
        }

        $traceHandle  = $this->handle->handle();
        $traceContent = $traceHandle->output();
        if (empty($traceContent)) {
            return $response;
        }

        $content = $response->getContent();

        $cssRoute = preg_replace('/\Ahttps?:/', '', route('debugger.assets.css'));
        $jsRoute  = preg_replace('/\Ahttps?:/', '', route('debugger.assets.js'));

        $style  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'  data-turbolinks-eval='false' data-turbo-eval='false'>";
        $script = "<script src='{$jsRoute}' type='text/javascript'  data-turbolinks-eval='false' data-turbo-eval='false' ></script>";

        $posCss = strripos($content, '</head>');
        if (false !== $posCss) {
            $content = substr($content, 0, $posCss) . PHP_EOL . $style . PHP_EOL . substr($content, $posCss);
        } else {
            $content = $style . PHP_EOL . $content;
        }

        $posJs = strripos($content, '</body>');
        if (false !== $posJs) {
            $content = substr($content, 0, $posJs) . PHP_EOL . $traceContent . PHP_EOL . $script . substr($content, $posJs);
            // set_protected_value($response, 'content', $traceContent);
        } else {
            $content = $content . PHP_EOL . $traceContent . PHP_EOL . $script;
        }

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        if ($original = $response->getOriginalContent()) {
            $response->original = $original;
        }

        return $response;
    }

    /**
     * 在响应发送到浏览器后处理任务。
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function terminate($request, $response)
    {
        // 测试发现 有时不会执行此方法，因此不能在此做各种「输出」
        return $response;
    }
}
