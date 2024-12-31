<?php

use zxf\Laravel\Trace\AssetController;

app('router')->prefix('debugger')->name('debugger.')->group(function ($router) {
    $router->get('assets/trace.css', [AssetController::class, 'css'])->name('trace.css');
    $router->get('assets/trace.js', [AssetController::class, 'js'])->name('trace.js');
});
