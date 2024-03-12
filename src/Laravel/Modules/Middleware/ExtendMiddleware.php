<?php

namespace zxf\Laravel\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use zxf\Laravel\Trace\Handle;

class ExtendMiddleware
{
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

        $traceHandle = '';
        $listenTrace = !app()->runningInConsole() && $request->isMethod('get') && config('modules.trace');
        if ($listenTrace) {
            $traceHandle = (new Handle($request))->handle();
        }

        $response = $next($request);

        // 在响应发送到浏览器前处理任务。
        if ($listenTrace && !empty($traceHandle)) {
            $traceContent = $traceHandle->output();

            // $pageContent = get_protected_value($response, 'content');
            $pageContent = $response->getContent();
            $position    = strripos($pageContent, "</html>");
            if (false !== $position) {
                // $pageContent = substr_replace($pageContent, $traceContent . PHP_EOL, $position, 0);
                $pageContent = substr($pageContent, 0, $position) . PHP_EOL . $traceContent . PHP_EOL . substr($pageContent, $position);
                // set_protected_value($response, 'content', $pageContent);
            } else {
                $pageContent = $pageContent . PHP_EOL . $traceContent;
            }
            $response->setContent($pageContent);
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
