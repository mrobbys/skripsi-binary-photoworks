<?php

use App\Domains\MasterData\Http\Controllers\AddonController;
use Illuminate\Support\Facades\Route;

Route::prefix('addon')
  ->name('addon.')
  ->group(function () {
    Route::get('/', [AddonController::class, 'index'])->name('index');
    Route::get('/data', [AddonController::class, 'data'])->name('data');
    Route::post('/', [AddonController::class, 'store'])->name('store');
    Route::put('/{addon}', [AddonController::class, 'update'])->name('update');
    Route::delete('/{addon}', [AddonController::class, 'destroy'])->name('destroy');
    Route::patch('/{addon}/toggle', [AddonController::class, 'toggleActive'])->name('toggle');
  });
