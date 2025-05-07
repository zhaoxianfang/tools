<?php

use zxf\TnCode\AssetController;
use zxf\Tools\Str;
use zxf\TnCode\Http\TnCodeController;

app('router')->prefix('tn_code')->name('tn_code.')->group(function ($router) {
    // 获取验证码图片
    $router->get('get_img', [TnCodeController::class, 'getImg'])->name('get_img');
    // 验证验证码结果
    $router->get('check', [TnCodeController::class, 'check'])->name('check');

    // 加载 css 、 js
    $router->get('assets/{filePath}', function ($filePath) {
        if (Str::endsWith($filePath, '.css')) {
            return AssetController::loadCss($filePath);
        }
        if (Str::endsWith($filePath, '.js')) {
            return AssetController::loadJs($filePath);
        }
        abort(404);
    });
    // 加载 png 图片
    $router->get('assets/{dir}/{filePath}', function ($dir, $filePath) {
        if (Str::endsWith($filePath, '.png')) {
            return AssetController::loadImg($dir . '/' . $filePath);
        }
        abort(404);
    });
});
