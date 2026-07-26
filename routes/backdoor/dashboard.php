<?php

use App\Domains\AdminDashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
  ->name('dashboard.')
  ->group(function () {
      Route::get('/', [DashboardController::class, 'index'])->name('index');
      Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
  });
