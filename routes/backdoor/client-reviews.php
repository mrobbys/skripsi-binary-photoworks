<?php

use App\Domains\Review\Http\Controllers\Backdoor\ReviewManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('client-reviews')
    ->name('client-reviews.')
    ->group(function () {
        // halaman index
        Route::get('/', [ReviewManagementController::class, 'index'])
            ->name('index');
        // JSON data untuk table
        Route::get('/data', [ReviewManagementController::class, 'data'])
            ->name('data');
        // JSON data untuk stats card
        Route::get('/stats', [ReviewManagementController::class, 'stats'])
            ->name('stats');
        // Action hapus review
        Route::delete('/{review}', [ReviewManagementController::class, 'destroy'])
            ->name('destroy');
    });