<?php

use App\Domains\MasterData\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/data-master/schedule')
  ->name('backdoor.data-master.schedule.')
  ->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::patch('/{schedule}', [ScheduleController::class, 'update'])->name('update');
    Route::patch('/{schedule}/toggle', [ScheduleController::class, 'toggleActive'])->name('toggle');
  });
