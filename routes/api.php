<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api')
    ->group(function () {
        Route::get('/products', [\Callcocam\Planogram\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
        Route::get('/categories', [\Callcocam\Planogram\Http\Controllers\Api\CategoryController::class, 'index'])->name('api.categories.index');
    });
