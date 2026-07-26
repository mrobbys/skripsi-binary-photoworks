<?php

use App\Domains\SystemSettings\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('activity-logs')
  ->name('activity-logs.')
  ->group(function () {
    // halaman index
    Route::get('/', [ActivityLogController::class, 'index'])
      ->name('index');
    // api json untuk table
    Route::get('/data', [ActivityLogController::class, 'data'])
      ->name('data');
  });
