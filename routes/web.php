<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'web'])
    ->prefix('planogram')->name('planogram.')
    ->group(function () {

        Route::post('/planogram', [\Callcocam\Planogram\Http\Controllers\PlanogramController::class, 'store'])->name('planogram.store');
        Route::put('/planogram/gondola/{planogram}', [\Callcocam\Planogram\Http\Controllers\PlanogramController::class, 'update'])->name('planogram.update');

        Route::resource('shelves', \Callcocam\Planogram\Http\Controllers\ShelfController::class)
            ->names('shelves');

        Route::resource('sections', \Callcocam\Planogram\Http\Controllers\SectionController::class)
            ->names('sections');

        Route::prefix('sections/{gondola}')->group(function () {
            Route::put('reorder', [\Callcocam\Planogram\Http\Controllers\SectionController::class, 'reorder'])->name('sections.reorder');
        });

        Route::put('/gondolas/{gondola}/scale-factor', [\Callcocam\Planogram\Http\Controllers\GondolaController::class, 'updateScaleFactor'])->name('gondolas.updateScaleFactor');
        // Add this route for section duplication
        Route::post('/sections/{section}/duplicate', [\Callcocam\Planogram\Http\Controllers\SectionController::class, 'duplicate'])->name('sections.duplicate');

        Route::put('/shelves/{shelf}/section', [\Callcocam\Planogram\Http\Controllers\ShelfController::class, 'updateSection'])->name('shelves.update-section');

        Route::resource('segments', \Callcocam\Planogram\Http\Controllers\SegmentController::class)
            ->names('segments');

        Route::put('/segments/{segment}/shelf', [\Callcocam\Planogram\Http\Controllers\SegmentController::class, 'shelfUpdate'])->name('segments.shelf-update');

        Route::resource('layers', \Callcocam\Planogram\Http\Controllers\LayerController::class)
            ->names('layers');
    });
