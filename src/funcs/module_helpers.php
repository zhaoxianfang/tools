<?php

if (!function_exists('module_path')) {
    function module_path($name, $path = '')
    {
        $modulePath = base_path(config('modules.namespace') . DIRECTORY_SEPARATOR . $name);
        return $modulePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('get_module_name')) {
    /**
     * 获取当前所在模块
     *
     * 在 Modules 模块里面 获取当前所在模块名称
     * 注意，需要在 Modules 里面调用，否则返回 App
     * @return mixed|string
     */
    function get_module_name()
    {
        try {
            // $uriArr = explode('/', request()->path());
            // return $uriArr[0];

            $routeNamespace = request()->route()->action['namespace'];
            $modulesNamespaceArr = array_filter(explode('\\', explode('Http\Controllers', $routeNamespace)[0]));
            return strtolower($modulesNamespaceArr[1]);
        } catch (\Exception $err) {
            return 'app';
        }
    }
}

if (!function_exists('listan_sql') && class_exists('\Illuminate\Support\Facades\DB')) {
    /**
     * 监听sql
     *
     * @param $traceLogStr 被引用字符串
     * @return void
     *
     * @demo
     *      // 开始监听
     *      listan_sql($logStrOrArr);
     *      // laravel 中间件
     *      $response = $next($request);
     *      // 打印sql追踪
     *      echo $logStr;
     */
    function listan_sql(&$traceLogStrOrArr = '',$carryHtml = true)
    {
        // 监听sql执行
        $style = $contentStyle ='';
        if($carryHtml){
            $style = "position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:'微软雅黑';background:#ffffff;";
            $contentStyle = 'overflow:auto;height:auto;padding:0;line-height: 24px;max-height: 200px;';
        }

        $debugInfo = [];
        $logStr = '';

        \Illuminate\Support\Facades\DB::listen(function ($query) use ($style, &$debugInfo, $contentStyle, &$logStr, &$traceLogStrOrArr,$carryHtml) {
            $bindings = $query->bindings;
            $sql = $query->sql;
            foreach ($bindings as $replace) {
                $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            $debugInfo[] = [
                'sql'  => $sql,
                'time' => round($query->time / 1000, 3),
            ];
            if($carryHtml) {
                foreach ($debugInfo as $info) {

                    $logStr .= '<li style="border-bottom:1px solid #EEE;font-size:14px;padding:0 12px">' . 'execution time:' . $info['time'] . '(s);=>sql:' . $info['sql'] . '</li>';
                }
                // 打印sql执行日志
                $traceLogStrOrArr = '<div style="' . $style . '"><div style="' . $contentStyle . '">' . $logStr . '</div></div>';
            }else{
                $traceLogStrOrArr = $debugInfo;
            }

        });
    }
}

if (!function_exists('copy_model') && class_exists('\Illuminate\Support\Facades\DB')) {
    /**
     * 复制一份不带 关联关系的模型
     * 复制模型，主要是解决 laravel -> replicate 复制方法会缺失部分字段问题
     * @param $model
     * @return mixed
     */
    function copy_model($model)
    {
        // 先复制一份模型 // 防止修改到原模型属性
        $copyModel = $model->replicate();
        // 创建一个新实例
        $obj = $model->newInstance();
        // 移除所有关联 relations
        $copyModel = $copyModel->unsetRelations();
        $modelObj  = collect($copyModel);
        foreach ($modelObj as $key => $item) {
            $obj->$key = $item;
        }
        return $obj;
    }
}
