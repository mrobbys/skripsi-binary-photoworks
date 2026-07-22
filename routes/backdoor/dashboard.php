<?php

use App\Domains\AdminDashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('backdoor/dashboard')
    ->name('backdoor.dashboard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    });
