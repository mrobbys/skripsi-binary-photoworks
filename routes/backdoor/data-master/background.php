<?php

use Illuminate\Support\Facades\Route;
use App\Domains\MasterData\Http\Controllers\BackgroundController;

Route::prefix('background')
  ->name('background.')
  ->group(function () {
    Route::get('/', [BackgroundController::class, 'index'])->name('index');
    Route::post('/', [BackgroundController::class, 'store'])->name('store');
    Route::post('/{background}', [BackgroundController::class, 'update'])->name('update');
    Route::delete('/{background}', [BackgroundController::class, 'destroy'])->name('destroy');
    Route::patch('/{background}/toggle', [BackgroundController::class, 'toggleActive'])->name('toggle');
  });
