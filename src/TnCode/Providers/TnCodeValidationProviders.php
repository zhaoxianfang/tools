<?php

namespace zxf\TnCode\Providers;

use Illuminate\Support\Facades\Validator;
use zxf\TnCode\TnCode;

/**
 * 为 TnCode 插件注册一个验证码验证器 TnCode
 */
class TnCodeValidationProviders extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // $attribute : 验证的字段名称
        // $value : 验证的值
        // $parameters : 一些参数 ,例如： 'tn_r' => 'required|TnCode:min,max', 里面的 min和max
        // $validator : Illuminate\Validation\Validator
        Validator::extend('TnCode', function ($attribute, $value, $parameters, $validator) {
            // 进行二次验证
            return (new TnCode)->recheck($value);
        });

        // 设置自定义验证消息
        Validator::replacer('TnCode', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, '滑动验证码验证失败');
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
