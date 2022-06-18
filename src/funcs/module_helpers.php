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