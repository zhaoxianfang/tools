<?php

use zxf\Laravel\Trace\AssetController;

app('router')->prefix('debugger')->name('debugger.')->group(function ($router) {
    $router->get('assets/stylesheets', [AssetController::class, 'css'])->name('assets.css');
    $router->get('assets/javascript', [AssetController::class, 'js'])->name('assets.js');
});
