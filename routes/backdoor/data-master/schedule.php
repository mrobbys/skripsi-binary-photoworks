<?php

use App\Domains\MasterData\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('schedule')
  ->name('schedule.')
  ->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::patch('/{schedule}', [ScheduleController::class, 'update'])->name('update');
    Route::patch('/{schedule}/toggle', [ScheduleController::class, 'toggleActive'])->name('toggle');
  });
