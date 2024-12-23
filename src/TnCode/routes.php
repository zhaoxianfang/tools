<?php

use zxf\TnCode\AssetController;

app('router')->prefix('tn_code')->name('tn_code.')->group(function ($router) {
    $router->get('assets/stylesheets', [AssetController::class, 'css'])->name('assets.css');
    $router->get('assets/javascript', [AssetController::class, 'js'])->name('assets.js');
    $router->get('img/{path}', [AssetController::class, 'img'])->name('assets.img');
});
