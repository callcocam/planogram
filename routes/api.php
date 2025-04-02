<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api/planogram')->name('planogram.api.')
    ->group(function () {
        Route::get('/products', [\Callcocam\Planogram\Http\Controllers\Api\ProductController::class, 'index'])->name('products.index');
        Route::get('/categories', [\Callcocam\Planogram\Http\Controllers\Api\CategoryController::class, 'index'])->name('categories.index');
        Route::resource('layers', \Callcocam\Planogram\Http\Controllers\Api\LayerController::class);
        Route::resource('segments', \Callcocam\Planogram\Http\Controllers\Api\SegmentController::class);
    });
