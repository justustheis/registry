<?php

use Illuminate\Support\Facades\Route;
use JustusTheis\Registry\Http\Controllers\RegistryController;

Route::prefix('registry')
    ->middleware([
        'web', 
        \JustusTheis\Registry\Http\Middleware\RegistryInertiaMiddleware::class,
        \JustusTheis\Registry\Http\Middleware\RegistryAuthorizationMiddleware::class
    ])
    ->as('registry.')
    ->group(function () {
        Route::get('/', [RegistryController::class, 'index'])->name('index');
        Route::post('/', [RegistryController::class, 'store'])->name('store');
        Route::put('/{key}', [RegistryController::class, 'update'])->name('update');
        Route::delete('/{key}', [RegistryController::class, 'destroy'])->name('destroy');
        Route::patch('/{key}/rename', [RegistryController::class, 'rename'])->name('rename');
    });
