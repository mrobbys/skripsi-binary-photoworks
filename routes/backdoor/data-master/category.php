<?php

use Illuminate\Support\Facades\Route;
use App\Domains\MasterData\Http\Controllers\CategoryController;

Route::middleware('auth')
  ->prefix('backdoor/data-master/category')
  ->name('backdoor.data-master.category.')
  ->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::put('{category:slug}', [CategoryController::class, 'update'])->name('update');
    Route::delete('{category:slug}', [CategoryController::class, 'destroy'])->name('destroy');
    Route::patch('{category:slug}/toggle', [CategoryController::class, 'toggleActive'])->name('toggle');
  });
