<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api/planogram')->name('planogram.api.')
    ->group(function () { 
        Route::resource('layers', \Callcocam\Planogram\Http\Controllers\Api\LayerController::class);
        Route::resource('segments', \Callcocam\Planogram\Http\Controllers\Api\SegmentController::class);
    });
